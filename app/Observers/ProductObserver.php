<?php

namespace App\Observers;

use App\Models\Product;
use App\Traits\EmployeeActivityTrait;
use App\Traits\UnitTypeSaveTrait;

class ProductObserver
{
    use EmployeeActivityTrait;
    use UnitTypeSaveTrait;

    public function saving(Product $product)
    {
        $this->unitType($product);

        if (! isRunningInConsoleOrSeeding()) {
            $product->last_updated_by = user() ? user()->id : null;
        }
    }

    public function created(Product $product)
    {
        if (! isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'product-created', $product->id, 'product');
        }
    }

    public function creating(Product $product)
    {
        \Illuminate\Support\Facades\Log::info('App\Observers\ProductObserver::creating called');

        if (! isRunningInConsoleOrSeeding()) {
            $product->added_by = user() ? user()->id : null;
        }

        if (company()) {
            $product->company_id = company()->id;
            \Illuminate\Support\Facades\Log::info('App\Observers\ProductObserver::creating - Set company_id to: ' . $product->company_id);
        } else {
            \Illuminate\Support\Facades\Log::warning('App\Observers\ProductObserver::creating - Company context missing!');
        }
    }

    public function updated(Product $product)
    {
        if (! isRunningInConsoleOrSeeding() && user()) {
            self::createEmployeeActivity(user()->id, 'product-updated', $product->id, 'product');
        }
    }

    public function deleted(Product $product)
    {
        if (user()) {
            self::createEmployeeActivity(user()->id, 'product-deleted');
        }
    }

    public function deleting(Product $product)
    {
        $product->files()->each(function ($file) {
            $file->delete();
        });
    }
}
