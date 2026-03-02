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
        Schema::dropIfExists('code_map_dependencies');
        Schema::dropIfExists('code_map_files');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to recreate, this is a cleanup migration
    }
};
