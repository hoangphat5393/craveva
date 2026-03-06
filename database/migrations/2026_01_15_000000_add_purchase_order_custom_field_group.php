<?php

use App\Models\Company;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Tạo nhóm custom field "Purchase Order" cho tất cả company hiện có
        Company::all()->each(function (Company $company) {
            CustomFieldGroup::withoutGlobalScopes()->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'name' => 'Purchase Order',
                ],
                [
                    'model' => 'Modules\\Purchase\\Entities\\PurchaseOrder',
                ]
            );
        });

        // Thêm một nhóm global (company_id = null) nếu cần dùng ở super admin
        CustomFieldGroup::withoutGlobalScopes()->firstOrCreate(
            [
                'company_id' => null,
                'name' => 'Purchase Order',
            ],
            [
                'model' => 'Modules\\Purchase\\Entities\\PurchaseOrder',
            ]
        );
    }

    public function down(): void
    {
        // Không xóa dữ liệu để tránh ảnh hưởng các custom field đã tạo
    }
};
