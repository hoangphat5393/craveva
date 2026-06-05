<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Maolin import header fields that duplicate computed estimate totals or other columns.
     */
    public function up(): void
    {
        Schema::table('estimates', function (Blueprint $table): void {
            $columns = [
                'quotation_date',
                'document_date',
                'exchange_rate',
                'header_quotation_amount',
                'header_tax_amount',
                'header_total_quantity',
                'confirm_internal',
                'confirm_customer',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('estimates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table): void {
            if (! Schema::hasColumn('estimates', 'quotation_date')) {
                $table->date('quotation_date')->nullable()->after('valid_till');
            }
            if (! Schema::hasColumn('estimates', 'document_date')) {
                $table->date('document_date')->nullable()->after('quotation_date');
            }
            if (! Schema::hasColumn('estimates', 'exchange_rate')) {
                $table->decimal('exchange_rate', 20, 6)->nullable()->after('document_date');
            }
            if (! Schema::hasColumn('estimates', 'header_quotation_amount')) {
                $table->decimal('header_quotation_amount', 16, 2)->nullable()->after('exchange_rate');
            }
            if (! Schema::hasColumn('estimates', 'header_tax_amount')) {
                $table->decimal('header_tax_amount', 16, 2)->nullable()->after('header_quotation_amount');
            }
            if (! Schema::hasColumn('estimates', 'header_total_quantity')) {
                $table->decimal('header_total_quantity', 16, 4)->nullable()->after('header_tax_amount');
            }
            if (! Schema::hasColumn('estimates', 'confirm_internal')) {
                $table->string('confirm_internal', 16)->nullable()->after('payment_terms_name');
            }
            if (! Schema::hasColumn('estimates', 'confirm_customer')) {
                $table->string('confirm_customer', 16)->nullable()->after('confirm_internal');
            }
        });
    }
};
