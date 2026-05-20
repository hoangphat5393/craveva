<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('estimate_bom_lines')) {
            return;
        }

        Schema::create('estimate_bom_lines', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('company_id')->index();
            $table->unsignedInteger('estimate_id')->index();
            $table->unsignedInteger('product_id')->nullable()->index();
            $table->string('material_name');
            $table->decimal('quantity', 16, 4);
            $table->unsignedInteger('unit_id')->nullable()->index();
            $table->decimal('unit_cost', 16, 4)->default(0);
            $table->decimal('line_total', 16, 4)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('estimate_id')
                ->references('id')
                ->on('estimates')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimate_bom_lines');
    }
};
