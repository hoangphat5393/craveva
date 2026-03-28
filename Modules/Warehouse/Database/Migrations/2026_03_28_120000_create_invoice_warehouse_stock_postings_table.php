<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoice_warehouse_stock_postings')) {
            return;
        }

        Schema::create('invoice_warehouse_stock_postings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable()->index();
            $table->unsignedInteger('invoice_id');
            $table->unsignedInteger('invoice_item_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedInteger('product_id');
            $table->decimal('quantity', 15, 4);
            $table->timestamps();

            $table->unique(['invoice_id', 'invoice_item_id'], 'iwsp_invoice_item_unique');
            $table->index(['invoice_id', 'company_id'], 'iwsp_invoice_company_idx');

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('invoice_item_id')->references('id')->on('invoice_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_warehouse_stock_postings');
    }
};
