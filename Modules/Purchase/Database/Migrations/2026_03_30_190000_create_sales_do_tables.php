<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales_dos')) {
            Schema::create('sales_dos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('legacy_sales_shipment_id')->nullable()->unique('sales_dos_legacy_unique');
                $table->unsignedInteger('company_id');
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('warehouse_id');
                $table->string('do_number', 64);
                $table->date('do_date');
                $table->enum('status', ['draft', 'confirmed', 'shipped', 'delivered', 'cancelled'])->default('draft');
                $table->boolean('outbound_stock_applied')->default(false);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'status', 'do_date'], 'sales_dos_company_status_date_idx');
                $table->unique(['company_id', 'do_number'], 'sales_dos_company_number_unique');
            });
        }

        if (! Schema::hasTable('sales_do_items')) {
            Schema::create('sales_do_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sales_do_id');
                $table->unsignedBigInteger('legacy_sales_shipment_item_id')->nullable()->unique('sales_do_items_legacy_unique');
                $table->unsignedBigInteger('order_item_id');
                $table->unsignedInteger('product_id')->nullable();
                $table->decimal('quantity_ordered', 20, 4)->default(0);
                $table->decimal('quantity_shipped', 20, 4)->default(0);
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('batch_number', 191)->nullable();
                $table->timestamps();

                $table->foreign('sales_do_id')->references('id')->on('sales_dos')->cascadeOnDelete();
                $table->index(['sales_do_id', 'order_item_id'], 'sales_do_items_do_item_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_do_items');
        Schema::dropIfExists('sales_dos');
    }
};
