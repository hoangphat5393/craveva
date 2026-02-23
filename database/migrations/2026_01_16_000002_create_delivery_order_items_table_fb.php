<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('delivery_order_items')) {
            Schema::create('delivery_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('delivery_order_id');
                $table->unsignedBigInteger('purchase_item_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->double('quantity_ordered')->default(0);
                $table->double('quantity_received')->default(0);
                $table->timestamps();
                $table->foreign('delivery_order_id')->references('id')->on('delivery_orders')->onDelete('cascade');
                // We don't add foreign key for purchase_item_id just in case, or we can if we are sure.
                // Given the codebase state, loose coupling might be safer, but let's try to be strict if possible.
                // But purchase_items table might be named differently or id might not be unsignedBigInteger (though likely is).
                // Let's stick to simple indices.
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_order_items');
    }
};
