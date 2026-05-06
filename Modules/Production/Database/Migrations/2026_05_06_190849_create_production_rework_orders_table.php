<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('production_rework_orders')) {
            return;
        }

        Schema::create('production_rework_orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->unsignedBigInteger('source_production_batch_id');
            $table->unsignedBigInteger('rework_production_order_id')->nullable();
            $table->decimal('requested_quantity', 20, 4);
            $table->decimal('approved_quantity', 20, 4)->nullable();
            $table->string('status', 32)->default('requested');
            $table->text('reason')->nullable();
            $table->text('decision_note')->nullable();
            $table->unsignedInteger('requested_by')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status'], 'production_rework_orders_company_status_idx');
            $table->index('source_production_batch_id', 'production_rework_orders_source_batch_idx');
            $table->index('rework_production_order_id', 'production_rework_orders_target_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_rework_orders');
    }
};
