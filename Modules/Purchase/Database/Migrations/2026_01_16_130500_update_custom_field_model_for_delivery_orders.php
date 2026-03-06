<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('custom_field_groups')
            ->where('model', 'App\\Models\\OrderDelivery')
            ->update(['model' => 'App\\Models\\DeliveryOrder']);

        DB::table('custom_fields_data')
            ->where('model', 'App\\Models\\OrderDelivery')
            ->update(['model' => 'App\\Models\\DeliveryOrder']);
    }

    public function down(): void
    {
        DB::table('custom_field_groups')
            ->where('model', 'App\\Models\\DeliveryOrder')
            ->update(['model' => 'App\\Models\\OrderDelivery']);

        DB::table('custom_fields_data')
            ->where('model', 'App\\Models\\DeliveryOrder')
            ->update(['model' => 'App\\Models\\OrderDelivery']);
    }
};
