<?php

use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove all custom fields for App\Models\Product
        if (class_exists(CustomFieldGroup::class) && class_exists(CustomField::class)) {
            $groups = CustomFieldGroup::where('model', 'App\Models\Product')->get();

            foreach ($groups as $group) {
                // Delete all custom fields in this group
                CustomField::where('custom_field_group_id', $group->id)->delete();

                // Optionally delete the group itself
                $group->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We cannot easily restore deleted data without a backup or re-running the original migrations.
        // Since this is a cleanup operation requested by the user, we leave down() empty or partial.
    }
};
