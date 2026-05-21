<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_sku_sequences')) {
            return;
        }

        Schema::create('product_sku_sequences', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('scope_key', 16);
            $table->unsignedBigInteger('last_value')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'scope_key']);
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sku_sequences');
    }
};
