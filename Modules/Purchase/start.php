<?php

use App\Models\Company;
use App\Models\CustomFieldGroup;

/*
|--------------------------------------------------------------------------
| Register Namespaces And Routes
|--------------------------------------------------------------------------
|
| When a module starting, this file will executed automatically. This helps
| to register some namespaces like translator or view. Also this file
| will load the routes file for each module. You may also modify
| this file as you want.
|
*/

require __DIR__.'/Routes/web.php';
Company::query()->select('id')->orderBy('id')->chunk(500, function ($companies) {
    foreach ($companies as $company) {
        CustomFieldGroup::firstOrCreate([
            'name' => 'Inventory',
            'model' => 'Modules\\Purchase\\Entities\\PurchaseInventory',
            'company_id' => $company->id,
        ]);
    }
});
