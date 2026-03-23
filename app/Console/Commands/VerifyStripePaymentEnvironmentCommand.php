<?php

namespace App\Console\Commands;

use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

/**
 * Trước khi test giao dịch: xác nhận Stripe (và Mollie) đang test hay live.
 * Không in full secret — chỉ prefix + vài ký tự cuối để đối chiếu.
 */
class VerifyStripePaymentEnvironmentCommand extends Command
{
    protected $signature = 'payment:stripe-verify';

    protected $description = 'Kiểm tra Stripe/Mollie đang test hay live (DB + key prefix) trước khi test thanh toán';

    public function handle(): int
    {
        $this->info('=== Kiểm tra môi trường thanh toán (an toàn QA) ===');
        $this->newLine();

        $this->line('APP_ENV:       ' . config('app.env'));
        $this->line('APP_URL:       ' . config('app.url'));
        $this->newLine();

        $global = null;
        try {
            $global = GlobalPaymentGatewayCredentials::query()->first();
        } catch (\Throwable $e) {
            $this->warn('Không đọc được global_payment_gateway_credentials: ' . $e->getMessage());
        }

        if ($global) {
            $this->table(
                ['Nguồn', 'Giá trị'],
                [
                    ['stripe_mode (DB global)', $global->stripe_mode ?? '(null)'],
                ]
            );
        } else {
            $this->warn('Không có bản ghi global_payment_gateway_credentials — Cashier dùng .env / fallback.');
        }

        $this->newLine();
        $this->info('Stripe — config hiệu lực (sau CustomConfigProvider)');
        $pk = config('cashier.key');
        $sk = config('cashier.secret');
        $pkMode = $this->modeFromStripePublishable($pk);
        $skMode = $this->modeFromStripeSecret($sk);

        $this->line('Publishable key: ' . $this->maskKey($pk) . '  → nhận diện: ' . $this->formatMode($pkMode));
        $this->line('Secret key:       ' . $this->maskKey($sk) . '  → nhận diện: ' . $this->formatMode($skMode));

        if ($pkMode !== 'empty' && $skMode !== 'empty' && $pkMode !== 'unknown' && $skMode !== 'unknown' && $pkMode !== $skMode) {
            $this->error('CẢNH BÁO: Publishable và Secret không cùng test/live — kiểm tra lại cấu hình.');
        }

        $stripeKeysMissing = ($pkMode === 'empty' && $skMode === 'empty');
        if ($stripeKeysMissing) {
            $this->warn('Stripe publishable/secret đang trống sau khi load config — kiểm tra Super Admin > Payment gateway hoặc STRIPE_KEY / STRIPE_SECRET trong .env.');
        }

        if ($global && isset($global->stripe_mode)) {
            $dbMode = $global->stripe_mode === 'test' ? 'test' : 'live';
            if (! $stripeKeysMissing) {
                if ($pkMode === 'test' && $dbMode === 'live') {
                    $this->error('NGUY HIỂM: DB đặt stripe_mode=live nhưng publishable key trông giống TEST (pk_test_).');
                }
                if ($pkMode === 'live' && $dbMode === 'test') {
                    $this->error('NGUY HIỂM: DB đặt stripe_mode=test nhưng publishable key trông giống LIVE (pk_live_) — có thể trừ tiền thật.');
                }
            }
            if ($dbMode === 'test') {
                $this->info('→ Theo DB global: đang chế độ TEST (khi đã có key: dùng Stripe test).');
            } elseif ($stripeKeysMissing) {
                $this->warn('→ DB global ghi stripe_mode=live nhưng chưa có key hiển thị — khi điền key live, giao dịch sẽ là thật.');
            } else {
                $this->warn('→ Theo DB global + key prefix: LIVE — giao dịch Stripe có thể trừ tiền thật.');
            }
        }

        $this->newLine();
        $this->info('Mollie — API key (config; có thể chỉ từ .env nếu chưa set qua admin)');
        $mollieKey = Config::get('mollie.key') ?? Config::get('mollie.api');
        if ($global && ! empty($global->mollie_api_key)) {
            $mollieKey = $global->mollie_api_key;
        }
        $mollieMode = $this->modeFromMollieKey($mollieKey);
        $this->line('Mollie key: ' . $this->maskKey($mollieKey) . '  → nhận diện: ' . $this->formatMode($mollieMode));
        if ($mollieMode === 'live') {
            $this->warn('→ Mollie key dạng LIVE — thanh toán thật.');
        } elseif ($mollieMode === 'test') {
            $this->info('→ Mollie key dạng test_ — môi trường test Mollie.');
        }

        $this->newLine();
        $this->comment('Ghi chú: từng company có payment_gateway_credentials riêng — trước khi test invoice, kiểm tra Stripe mode trong Admin > Payment gateway của company đó.');
        $this->newLine();

        return self::SUCCESS;
    }

    private function modeFromStripePublishable(?string $key): string
    {
        if ($key === null || $key === '') {
            return 'empty';
        }
        if (str_starts_with($key, 'pk_test_')) {
            return 'test';
        }
        if (str_starts_with($key, 'pk_live_')) {
            return 'live';
        }

        return 'unknown';
    }

    private function modeFromStripeSecret(?string $key): string
    {
        if ($key === null || $key === '') {
            return 'empty';
        }
        if (str_starts_with($key, 'sk_test_')) {
            return 'test';
        }
        if (str_starts_with($key, 'sk_live_')) {
            return 'live';
        }

        return 'unknown';
    }

    private function modeFromMollieKey(?string $key): string
    {
        if ($key === null || $key === '') {
            return 'empty';
        }
        if (str_starts_with($key, 'test_')) {
            return 'test';
        }
        if (str_starts_with($key, 'live_')) {
            return 'live';
        }

        return 'unknown';
    }

    private function formatMode(string $mode): string
    {
        return match ($mode) {
            'test' => 'TEST (an toàn — không tiền thật)',
            'live' => 'LIVE (tiền thật)',
            'empty' => '(trống)',
            default => 'không nhận diện được prefix chuẩn',
        };
    }

    private function maskKey(?string $key): string
    {
        if ($key === null || $key === '') {
            return '(trống)';
        }
        $len = strlen($key);
        if ($len <= 16) {
            return substr($key, 0, 8) . '…(' . strlen($key) . ' ký tự)';
        }

        return substr($key, 0, 12) . '…' . substr($key, -4);
    }
}
