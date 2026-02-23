<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CompanyProductsSeeder extends Seeder
{
    public function run(): void
    {
        config(['app.seeding' => true]);

        $companies = Company::all();

        foreach ($companies as $company) {
            Product::factory()
                ->count(5)
                ->make()
                ->each(function (Product $product) use ($company) {
                    $product->company_id = $company->id;
                    $product->save();
                });
        }

        config(['app.seeding' => false]);
    }
}

