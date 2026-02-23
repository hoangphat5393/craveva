<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('deal_proposal_pricing')) {
            Schema::create('deal_proposal_pricing', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('proposal_id')->index();
                $table->unsignedBigInteger('pricing_tier_id')->nullable()->index();
                $table->enum('applied_discount_type', ['percentage', 'fixed_amount', 'override_price'])->nullable();
                $table->decimal('applied_discount_value', 15, 4)->nullable();
                $table->boolean('volume_discount_applied')->default(false);
                $table->boolean('custom_pricing_applied')->default(false);
                $table->timestamps();

                $table->foreign('proposal_id')->references('id')->on('proposals')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign('pricing_tier_id')->references('id')->on('pricing_tiers')->onUpdate('cascade')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_proposal_pricing');
    }
};
