<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales_histories')) {
            Schema::create('sales_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->string('source_filename', 512)->nullable();
                $table->unsignedBigInteger('imported_by')->nullable()->index();
                $table->timestamp('imported_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sales_history_lines')) {
            Schema::create('sales_history_lines', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id')->index();
                $table->unsignedBigInteger('sales_history_id')->nullable()->index();
                $table->date('shipment_date');
                $table->unsignedBigInteger('client_id')->index()->comment('users.id');
                $table->unsignedBigInteger('client_details_id')->nullable()->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->decimal('quantity', 30, 6);
                $table->decimal('quantity_abs', 30, 6);
                $table->decimal('amount', 30, 6)->nullable();
                $table->decimal('unit_price', 30, 6)->nullable();
                $table->boolean('is_return')->default(false);
                $table->unsignedBigInteger('currency_id')->nullable()->index();
                $table->string('source_sheet_name', 191)->nullable();
                $table->string('source_row_hash', 64);
                $table->decimal('net_sales_volume_raw', 30, 6)->nullable();
                $table->decimal('net_sales_amount_raw', 30, 6)->nullable();
                $table->timestamps();

                $table->unique(['company_id', 'source_row_hash'], 'sales_history_lines_company_hash_unique');
                $table->index(['company_id', 'shipment_date']);
                $table->index(['company_id', 'product_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_history_lines');
        Schema::dropIfExists('sales_histories');
    }
};
