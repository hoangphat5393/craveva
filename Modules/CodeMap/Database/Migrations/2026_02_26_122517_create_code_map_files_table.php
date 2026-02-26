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
        Schema::create('code_map_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path')->unique();
            $table->string('language')->nullable();
            $table->string('framework')->nullable();
            $table->string('role')->nullable();
            $table->string('module')->nullable();
            $table->string('version')->nullable();
            $table->timestamp('last_modified_at')->nullable();
            $table->string('hash', 64)->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_map_files');
    }
};
