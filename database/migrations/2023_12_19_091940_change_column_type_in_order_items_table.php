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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('sub_total', 30, 2)->change();
            $table->decimal('total', 30, 2)->change();
            $table->decimal('discount', 30, 2)->default(0)->change();
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('unit_price', 30, 2)->change();
            $table->decimal('quantity', 30, 2)->change();
        });
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->decimal('adjustment_amount', 30, 2)->nullable()->change();
            $table->decimal('sub_total', 30, 2)->change();
            $table->decimal('total', 30, 2)->change();
            $table->decimal('discount', 30, 2)->default(0)->change();
        });
        Schema::table('credit_note_items', function (Blueprint $table) {
            $table->decimal('unit_price', 30, 2)->change();
            $table->decimal('amount', 30, 2)->change();
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('due_amount', 30, 2)->default(0)->change();
            $table->decimal('discount', 30, 2)->default(0)->change();
            $table->decimal('total', 30, 2)->change();
            $table->decimal('sub_total', 30, 2)->change();
        });
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('quantity', 30, 2)->change();
            $table->decimal('unit_price', 30, 2)->change();
            $table->decimal('amount', 30, 2)->change();
        });
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('sub_total', 30, 2)->change();
            $table->decimal('total', 30, 2)->change();
        });
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('amount', 30, 2)->change();
        });
        Schema::table('estimates', function (Blueprint $table) {
            $table->decimal('sub_total', 30, 2)->change();
            $table->decimal('total', 30, 2)->change();
            $table->decimal('discount', 30, 2)->default(0)->change();
        });
        Schema::table('estimate_templates', function (Blueprint $table) {
            $table->decimal('sub_total', 30, 2)->change();
            $table->decimal('total', 30, 2)->change();
            $table->decimal('discount', 30, 2)->change();
        });
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->decimal('quantity', 30, 2)->change();
            $table->decimal('unit_price', 30, 2)->change();
            $table->decimal('amount', 30, 2)->change();
        });
        Schema::table('estimate_template_items', function (Blueprint $table) {
            $table->decimal('quantity', 30, 2)->change();
            $table->decimal('unit_price', 30, 2)->change();
            $table->decimal('amount', 30, 2)->change();
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('price', 30, 2)->change();
        });
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->decimal('cost', 30, 2)->change();
        });
        Schema::table('proposals', function (Blueprint $table) {
            $table->decimal('sub_total', 30, 2)->change();
            $table->decimal('total', 30, 2)->change();
            $table->decimal('discount', 30, 2)->change();
        });
        Schema::table('proposal_items', function (Blueprint $table) {
            $table->decimal('quantity', 30, 2)->change();
            $table->decimal('unit_price', 30, 2)->change();
            $table->decimal('amount', 30, 2)->change();
        });
        Schema::table('proposal_templates', function (Blueprint $table) {
            $table->decimal('sub_total', 30, 2)->change();
            $table->decimal('total', 30, 2)->change();
            $table->decimal('discount', 30, 2)->change();
        });
        Schema::table('proposal_template_items', function (Blueprint $table) {
            $table->decimal('unit_price', 30, 2)->change();
            $table->decimal('amount', 30, 2)->change();
            $table->decimal('quantity', 30, 2)->change();
        });
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->decimal('bank_balance', 30, 2)->nullable()->change();
            $table->decimal('opening_balance', 30, 2)->nullable()->change();
        });
        Schema::table('bank_transactions', function (Blueprint $table) {
            $table->decimal('bank_balance', 30, 2)->nullable()->change();
            $table->decimal('amount', 30, 2)->nullable()->change();
        });
        Schema::table('order_carts', function (Blueprint $table) {
            $table->decimal('quantity', 30, 2)->change();
            $table->decimal('unit_price', 30, 2)->change();
            $table->decimal('amount', 30, 2)->change();
        });
        Schema::table('expenses_recurring', function (Blueprint $table) {
            $table->decimal('price', 30, 2)->change();
        });
        Schema::table('invoice_recurring', function (Blueprint $table) {
            $table->decimal('sub_total', 30, 2)->default(0)->change();
            $table->decimal('total', 30, 2)->default(0)->change();
            $table->decimal('discount', 30, 2)->default(0)->change();
        });
        Schema::table('invoice_recurring_items', function (Blueprint $table) {
            $table->decimal('quantity', 30, 2)->change();
            $table->decimal('unit_price', 30, 2)->change();
            $table->decimal('amount', 30, 2)->change();
        });

        if (Schema::hasColumn('leads', 'value')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->decimal('value', 30, 2)->nullable()->default(0)->change();
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 30, 2)->change();
        });
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('project_budget', 30, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
