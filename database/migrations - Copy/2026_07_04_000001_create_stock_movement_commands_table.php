<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_movement_commands')) {
            return;
        }

        Schema::create('stock_movement_commands', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('movement_type', 32);
            $table->string('idempotency_key', 100);
            $table->timestamps();

            $table->unique(
                ['company_id', 'movement_type', 'idempotency_key'],
                'stock_movement_command_unique'
            );
            $table->index(['company_id', 'created_at'], 'stock_movement_command_company_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movement_commands');
    }
};
