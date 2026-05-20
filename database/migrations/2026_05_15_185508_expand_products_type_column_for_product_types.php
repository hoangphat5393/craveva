<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'type')) {
            return;
        }

        DB::statement("ALTER TABLE `products` MODIFY COLUMN `type` VARCHAR(32) NULL DEFAULT 'goods'");

        DB::table('products')->where('type', '')->update(['type' => 'goods']);
        DB::table('products')->whereNull('type')->update(['type' => 'goods']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'type')) {
            return;
        }

        DB::table('products')
            ->whereNotIn('type', ['goods', 'service'])
            ->update(['type' => 'goods']);

        DB::statement("ALTER TABLE `products` MODIFY COLUMN `type` ENUM('goods', 'service') NULL DEFAULT 'goods'");
    }
};
