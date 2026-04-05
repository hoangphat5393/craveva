<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->date('quotation_date')->nullable()->after('valid_till');
            $table->date('document_date')->nullable()->after('quotation_date');
            $table->decimal('exchange_rate', 20, 6)->nullable()->after('document_date');
            $table->decimal('header_quotation_amount', 16, 2)->nullable()->after('exchange_rate');
            $table->decimal('header_tax_amount', 16, 2)->nullable()->after('header_quotation_amount');
            $table->decimal('header_total_quantity', 16, 4)->nullable()->after('header_tax_amount');
            $table->text('delivery_note')->nullable()->after('header_total_quantity');
            $table->string('salesperson_name', 191)->nullable()->after('delivery_note');
            $table->string('tax_type_label', 191)->nullable()->after('salesperson_name');
            $table->string('payment_terms_code', 64)->nullable()->after('tax_type_label');
            $table->string('payment_terms_name', 255)->nullable()->after('payment_terms_code');
            $table->string('confirm_internal', 16)->nullable()->after('payment_terms_name');
            $table->string('confirm_customer', 16)->nullable()->after('confirm_internal');
            $table->string('price_terms', 255)->nullable()->after('confirm_customer');
            $table->string('volume_unit', 64)->nullable()->after('price_terms');
            $table->decimal('total_gross_weight_kg', 16, 4)->nullable()->after('volume_unit');
            $table->decimal('total_volume', 16, 4)->nullable()->after('total_gross_weight_kg');
        });

        Schema::table('estimate_items', function (Blueprint $table) {
            $table->decimal('free_quantity', 16, 4)->nullable()->after('quantity');
            $table->date('line_effective_date')->nullable()->after('free_quantity');
            $table->date('line_expiry_date')->nullable()->after('line_effective_date');
        });
    }

    public function down(): void
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->dropColumn([
                'free_quantity',
                'line_effective_date',
                'line_expiry_date',
            ]);
        });

        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn([
                'quotation_date',
                'document_date',
                'exchange_rate',
                'header_quotation_amount',
                'header_tax_amount',
                'header_total_quantity',
                'delivery_note',
                'salesperson_name',
                'tax_type_label',
                'payment_terms_code',
                'payment_terms_name',
                'confirm_internal',
                'confirm_customer',
                'price_terms',
                'volume_unit',
                'total_gross_weight_kg',
                'total_volume',
            ]);
        });
    }
};
