<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('warehouse_product_batches')) {
            Schema::create('warehouse_product_batches', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedInteger('product_id');
                $table->string('batch_number')->nullable();
                $table->date('expiration_date')->nullable();
                $table->date('manufacturing_date')->nullable();
                $table->decimal('quantity', 15, 4)->default(0);
                $table->decimal('reserved_quantity', 15, 4)->default(0);
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null')->onUpdate('cascade');
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');

                $table->index(['warehouse_id', 'product_id'], 'wpb_wh_product_idx');
                $table->index(['company_id', 'warehouse_id', 'product_id', 'batch_number', 'expiration_date'], 'wpb_lookup_idx');
            });
        }

        if (! Schema::hasTable('stock_reservations')) {
            Schema::create('stock_reservations', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->unsignedBigInteger('warehouse_id');
                $table->unsignedInteger('product_id');
                $table->string('batch_number')->nullable();
                $table->date('expiration_date')->nullable();
                $table->decimal('reserved_quantity', 15, 4);
                $table->string('reference_type', 50)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('status', 20)->default('active'); // active, released, consumed
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null')->onUpdate('cascade');
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade')->onUpdate('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');

                $table->index(['warehouse_id', 'product_id'], 'stock_reservation_wh_product_idx');
                $table->index(['company_id', 'warehouse_id', 'product_id', 'batch_number', 'expiration_date', 'status'], 'stock_reservation_lookup_idx');
                $table->index(['reference_type', 'reference_id'], 'stock_reservation_ref_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('warehouse_product_batches');
    }
};
