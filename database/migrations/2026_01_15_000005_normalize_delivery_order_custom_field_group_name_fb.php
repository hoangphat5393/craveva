<?php

use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    public function up(): void
    {
        // Đổi tên các nhóm custom field cũ từ 'app.delivery order' thành 'Delivery Order'
        CustomFieldGroup::withoutGlobalScopes()
            ->where('name', 'app.delivery order')
            ->update(['name' => 'Delivery Order']);

        // Chuẩn hoá lại tên cho mọi nhóm gắn với model Delivery Order (nếu đã tồn tại)
        CustomFieldGroup::withoutGlobalScopes()
            ->where('model', 'App\\Models\\OrderDelivery')
            ->where('name', '!=', 'Delivery Order')
            ->update(['name' => 'Delivery Order']);
    }

    public function down(): void
    {
        // Không rollback để tránh ghi đè tên đã sửa
    }
};

