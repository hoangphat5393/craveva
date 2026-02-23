<?php

use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        CustomFieldGroup::withoutGlobalScopes()
            ->where('model', 'Modules\\Purchase\\Entities\\PurchaseOrder')
            ->where('name', '!=', 'Purchase Order')
            ->update(['name' => 'Purchase Order']);
    }

    public function down(): void
    {
    }
};

