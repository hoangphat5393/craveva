<?php

namespace Modules\Pricing\Jobs;

use App\Models\ClientDetails;
use App\Models\Product;
use App\Models\User;
use App\Traits\ExcelImportable;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Modules\Pricing\Entities\ClientProductPricing;

class ImportClientProductPricingJob implements ShouldQueue
{
    use Batchable, Dispatchable, ExcelImportable, InteractsWithQueue, Queueable, SerializesModels;

    private $row;

    private $columns;

    private $company;

    public function __construct($row, $columns, $company = null)
    {
        $this->row = $row;
        $this->columns = $columns;
        $this->company = $company;
    }

    public function handle(): void
    {
        $customerCode = $this->getColumnValue('customer_code');
        $email = $this->getColumnValue('email');
        $sku = $this->getColumnValue('product_sku');
        $customPrice = $this->getColumnValue('custom_price');
        $discountType = $this->getColumnValue('discount_type');
        $discountValue = $this->getColumnValue('discount_value');
        $startDate = $this->parseImportDate($this->getColumnValue('start_date'), now()->toDateString());
        $endDate = $this->parseImportDate($this->getColumnValue('end_date'), '2099-12-31');

        $clientDetails = null;

        if (! empty($customerCode)) {
            $clientDetails = ClientDetails::where('company_id', $this->company?->id)
                ->where('client_code', $customerCode)
                ->first();
        }

        if (! $clientDetails && $this->isEmailValid($email)) {
            $user = User::where('email', $email)
                ->where('company_id', $this->company?->id)
                ->first();
            $clientDetails = $user ? ClientDetails::where('company_id', $this->company?->id)->where('user_id', $user->id)->first() : null;
        }

        if (! $clientDetails) {
            $this->failJobWithMessage('Client not found: ');

            return;
        }

        if (empty($sku)) {
            $this->failJobWithMessage('Product SKU missing: ');

            return;
        }

        $product = Product::where('company_id', $this->company?->id)
            ->where('sku', $sku)
            ->first();

        if (! $product) {
            $this->failJob('Product not found: ');

            return;
        }

        $pricing = ClientProductPricing::firstOrNew([
            'client_id' => $clientDetails->user_id,
            'product_id' => $product->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $pricing->company_id = $this->company ? $this->company->id : null;
        $pricing->custom_price = $customPrice !== null && $customPrice !== '' ? (float) $customPrice : null;
        $pricing->discount_type = $discountType ?: null;
        $pricing->discount_value = $discountValue !== null && $discountValue !== '' ? (float) $discountValue : null;
        $pricing->start_date = $startDate;
        $pricing->end_date = $endDate;
        $pricing->is_active = true;
        $pricing->save();
    }

    private function parseImportDate($value, string $default): string
    {
        if ($value === null || $value === '') {
            return $default;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return $default;
        }
    }
}
