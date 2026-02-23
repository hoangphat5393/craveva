<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('discount_rules');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('discount_rules')) {
            Schema::create('discount_rules', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
                
                $table->string('name');
                $table->enum('trigger_type', ['quantity', 'total_amount'])->default('quantity');
                $table->decimal('trigger_min', 12, 2)->default(0);
                $table->decimal('trigger_max', 12, 2)->nullable();
                
                $table->enum('discount_type', ['percentage', 'fixed_amount'])->default('percentage');
                $table->decimal('discount_value', 12, 2);
                
                $table->enum('scope', ['global', 'product', 'category'])->default('global');
                $table->unsignedInteger('product_id')->nullable();
                $table->unsignedInteger('category_id')->nullable();
                
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }
    }
};
