<?php

use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $this->fixDuplicateGroupsForName('Product', 'App\\Models\\Product');
        $this->fixDuplicateGroupsForName('Purchase Order', 'Modules\\Purchase\\Entities\\PurchaseOrder');
    }

    private function fixDuplicateGroupsForName(string $name, string $expectedModel): void
    {
        $groups = CustomFieldGroup::withoutGlobalScopes()
            ->where('name', $name)
            ->orderBy('company_id')
            ->orderBy('id')
            ->get()
            ->groupBy(function (CustomFieldGroup $group) {
                return (string) ($group->company_id ?? 'null');
            });

        foreach ($groups as $companyGroups) {
            if ($companyGroups->isEmpty()) {
                continue;
            }

            if ($companyGroups->count() === 1) {
                $single = $companyGroups->first();

                if ($single && $single->model !== $expectedModel) {
                    $single->model = $expectedModel;
                    $single->save();
                }

                continue;
            }

            /** @var CustomFieldGroup|null $canonical */
            $canonical = $companyGroups->firstWhere('model', $expectedModel) ?: $companyGroups->first();

            if (! $canonical) {
                continue;
            }

            if ($canonical->model !== $expectedModel) {
                $canonical->model = $expectedModel;
                $canonical->save();
            }

            $duplicates = $companyGroups->filter(function (CustomFieldGroup $group) use ($canonical) {
                return $group->id !== $canonical->id;
            });

            foreach ($duplicates as $duplicate) {
                CustomField::where('custom_field_group_id', $duplicate->id)
                    ->update(['custom_field_group_id' => $canonical->id]);

                $duplicate->delete();
            }
        }
    }

    public function down(): void
    {
        // Không rollback xoá trùng để tránh làm sai lệch dữ liệu custom field hiện tại
    }
};
