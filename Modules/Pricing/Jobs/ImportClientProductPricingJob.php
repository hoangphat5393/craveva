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
use Modules\Pricing\Entities\ClientProductPricing;

class ImportClientProductPricingJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ExcelImportable;

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

        $clientDetails = null;

        if (!empty($customerCode)) {
            $clientDetails = ClientDetails::where('client_code', $customerCode)->first();
        }

        if (!$clientDetails && $this->isEmailValid($email)) {
            $user = User::where('email', $email)->first();
            $clientDetails = $user ? ClientDetails::where('user_id', $user->id)->first() : null;
        }

        if (!$clientDetails) {
            $this->failJobWithMessage('Client not found: ');
            return;
        }

        if (empty($sku)) {
            $this->failJobWithMessage('Product SKU missing: ');
            return;
        }

        $product = Product::where('sku', $sku)->first();

        if (!$product) {
            $this->failJob('Product not found: ');
            return;
        }

        $pricing = ClientProductPricing::firstOrNew([
            'client_id' => $clientDetails->user_id,
            'product_id' => $product->id,
        ]);

        $pricing->company_id = $this->company ? $this->company->id : null;
        $pricing->custom_price = $customPrice !== null && $customPrice !== '' ? (float) $customPrice : null;
        $pricing->discount_type = $discountType ?: null;
        $pricing->discount_value = $discountValue !== null && $discountValue !== '' ? (float) $discountValue : null;
        $pricing->is_active = true;
        $pricing->save();
    }
}
