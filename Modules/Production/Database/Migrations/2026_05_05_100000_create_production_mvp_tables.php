<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('production_boms')) {
            Schema::create('production_boms', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->unsignedInteger('output_product_id');
                $table->string('version', 32);
                $table->string('code', 64)->nullable();
                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();
                $table->boolean('is_default')->default(false);
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete()->cascadeOnUpdate();
                $table->foreign('output_product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
                $table->index(['company_id', 'output_product_id', 'version'], 'production_boms_company_product_version_idx');
            });
        }

        if (! Schema::hasTable('production_bom_items')) {
            Schema::create('production_bom_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->unsignedBigInteger('production_bom_id');
                $table->unsignedInteger('component_product_id');
                $table->decimal('quantity', 15, 4);
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete()->cascadeOnUpdate();
                $table->foreign('production_bom_id')->references('id')->on('production_boms')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreign('component_product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
                $table->index('production_bom_id', 'production_bom_items_bom_idx');
            });
        }

        if (! Schema::hasTable('production_orders')) {
            Schema::create('production_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->string('status', 32)->default('draft');
                $table->unsignedInteger('output_product_id');
                $table->unsignedBigInteger('production_bom_id')->nullable();
                $table->unsignedBigInteger('rm_warehouse_id');
                $table->unsignedBigInteger('fg_warehouse_id');
                $table->decimal('planned_quantity', 15, 4);
                $table->unsignedBigInteger('sales_order_id')->nullable();
                $table->unsignedBigInteger('project_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamp('released_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete()->cascadeOnUpdate();
                $table->foreign('output_product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('production_bom_id')->references('id')->on('production_boms')->nullOnDelete()->cascadeOnUpdate();
                $table->foreign('rm_warehouse_id')->references('id')->on('warehouses')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('fg_warehouse_id')->references('id')->on('warehouses')->cascadeOnUpdate()->cascadeOnDelete();
                $table->index(['company_id', 'status'], 'production_orders_company_status_idx');
            });
        }

        if (! Schema::hasTable('production_batches')) {
            Schema::create('production_batches', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->unsignedBigInteger('production_order_id');
                $table->string('batch_code', 64);
                $table->text('notes')->nullable();
                $table->timestamp('posted_consumptions_at')->nullable();
                $table->timestamp('posted_receipt_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete()->cascadeOnUpdate();
                $table->foreign('production_order_id')->references('id')->on('production_orders')->cascadeOnDelete()->cascadeOnUpdate();
                $table->unique(['company_id', 'batch_code'], 'production_batches_company_batch_code_unique');
            });
        }

        if (! Schema::hasTable('production_batch_consumptions')) {
            Schema::create('production_batch_consumptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->unsignedBigInteger('production_batch_id');
                $table->unsignedInteger('component_product_id');
                $table->unsignedBigInteger('warehouse_product_batch_id')->nullable();
                $table->decimal('planned_quantity', 15, 4);
                $table->decimal('actual_quantity', 15, 4)->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->unsignedInteger('line_order')->default(0);
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete()->cascadeOnUpdate();
                $table->foreign('production_batch_id')->references('id')->on('production_batches')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreign('component_product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('warehouse_product_batch_id')->references('id')->on('warehouse_product_batches')->nullOnDelete()->cascadeOnUpdate();
                $table->index('production_batch_id', 'production_batch_consumptions_batch_idx');
            });
        }

        if (! Schema::hasTable('production_batch_outputs')) {
            Schema::create('production_batch_outputs', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable();
                $table->unsignedBigInteger('production_batch_id');
                $table->unsignedInteger('output_product_id');
                $table->decimal('quantity', 15, 4);
                $table->string('batch_number', 191);
                $table->date('expiration_date')->nullable();
                $table->date('manufacturing_date')->nullable();
                $table->unsignedBigInteger('warehouse_id');
                $table->timestamp('posted_at')->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete()->cascadeOnUpdate();
                $table->foreign('production_batch_id')->references('id')->on('production_batches')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreign('output_product_id')->references('id')->on('products')->cascadeOnUpdate()->cascadeOnDelete();
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnUpdate()->cascadeOnDelete();
                $table->index('production_batch_id', 'production_batch_outputs_batch_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('production_batch_outputs');
        Schema::dropIfExists('production_batch_consumptions');
        Schema::dropIfExists('production_batches');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('production_bom_items');
        Schema::dropIfExists('production_boms');
    }
};
