<?php

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

require __DIR__ . '/Routes/web.php';
\App\Models\CustomFieldGroup::withoutGlobalScope(\App\Scopes\CompanyScope::class);
if (!\App\Models\CustomFieldGroup::where('name', 'Inventory')->exists()) {
    $companies = \App\Models\Company::select('id')->get();
    $groups = [];
    foreach ($companies as $company) {
        $groups[] = [
            'name' => 'Inventory',
            'model' => 'Modules\\Purchase\\Entities\\PurchaseInventory',
            'company_id' => $company->id,
        ];
    }
    if (!empty($groups)) {
        \App\Models\CustomFieldGroup::insert($groups);
    }
}
