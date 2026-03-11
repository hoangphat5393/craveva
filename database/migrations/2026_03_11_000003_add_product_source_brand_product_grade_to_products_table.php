<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Product Source (nguồn sản phẩm), Brand (thương hiệu), Product Grade (cấp sản phẩm).
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'product_source')) {
                $table->string('product_source', 255)->nullable()->after('specification');
            }
            if (! Schema::hasColumn('products', 'brand')) {
                $table->string('brand', 255)->nullable()->after('product_source');
            }
            if (! Schema::hasColumn('products', 'product_grade')) {
                $table->string('product_grade', 255)->nullable()->after('brand');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'product_source')) {
                $table->dropColumn('product_source');
            }
            if (Schema::hasColumn('products', 'brand')) {
                $table->dropColumn('brand');
            }
            if (Schema::hasColumn('products', 'product_grade')) {
                $table->dropColumn('product_grade');
            }
        });
    }
};
