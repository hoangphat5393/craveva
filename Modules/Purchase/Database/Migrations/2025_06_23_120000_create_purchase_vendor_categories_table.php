<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Required before 2025_06_23_124939_add_category_id_in_vendor_table (FK to this table).
     */
    public function up(): void
    {
        if (Schema::hasTable('purchase_vendor_categories')) {
            return;
        }

        Schema::create('purchase_vendor_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->string('category_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_vendor_categories');
    }
};
