<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `employee_details` DROP FOREIGN KEY `employee_details_designation_id_foreign`;');
        DB::statement('ALTER TABLE `employee_details` ADD CONSTRAINT `employee_details_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `designations`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
