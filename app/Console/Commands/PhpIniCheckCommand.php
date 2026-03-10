<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Hiển thị thông tin php.ini đang dùng (khi chạy qua CLI).
 * Để xem php.ini cho request WEB (trình duyệt), truy cập: /php-ini-check (khi APP_DEBUG=true).
 */
class PhpIniCheckCommand extends Command
{
    protected $signature = 'php-ini:check';

    protected $description = 'Kiểm tra thông tin php.ini đang dùng (CLI)';

    public function handle(): int
    {
        $this->info('=== PHP ini (CLI - khi chạy lệnh terminal) ===');
        $this->newLine();

        $loaded = php_ini_loaded_file();
        $this->line('File php.ini: ' . ($loaded ?: '(không tìm thấy)'));

        $scanned = php_ini_scanned_files();
        if ($scanned) {
            $this->line('File scan thêm: ' . $scanned);
        }

        $this->newLine();
        $this->info('Một số directive liên quan import:');
        $this->table(
            ['Directive', 'Value'],
            [
                ['max_execution_time', ini_get('max_execution_time') . ' (CLI thường = 0)'],
                ['max_input_time', ini_get('max_input_time')],
                ['memory_limit', ini_get('memory_limit')],
                ['max_input_vars', ini_get('max_input_vars')],
            ]
        );

        $this->newLine();
        $this->comment('Lưu ý: Request import (trình duyệt) dùng php.ini của PHP-FPM/Apache, không phải CLI.');
        $this->comment('Để xem php.ini cho WEB: mở trình duyệt truy cập ' . config('app.url') . '/php-ini-check (cần APP_DEBUG=true)');

        return self::SUCCESS;
    }
}
