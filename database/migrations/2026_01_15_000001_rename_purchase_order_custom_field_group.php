<?php

use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Đổi tên các nhóm custom field cũ từ 'app.purchase order' thành 'Purchase Order'
        CustomFieldGroup::withoutGlobalScopes()
            ->where('name', 'app.purchase order')
            ->update(['name' => 'Purchase Order']);
    }

    public function down(): void
    {
        // Không rollback để tránh ghi đè tên đã sửa
    }
};
