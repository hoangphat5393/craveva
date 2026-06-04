<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'cost_from_bom')) {
                $table->boolean('cost_from_bom')->default(false)->after('purchase_price');
            }
        });

        if (Schema::hasColumn('products', 'purchase_information')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('purchase_information');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'purchase_information')) {
                $table->enum('purchase_information', ['1', '0'])->default('0')->after('purchase_price');
            }
        });

        if (Schema::hasColumn('products', 'cost_from_bom')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropColumn('cost_from_bom');
            });
        }
    }
};
