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
        Schema::create('code_map_dependencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_id');
            $table->unsignedBigInteger('depends_on_file_id');
            $table->string('relation_type')->nullable();
            $table->timestamps();

            $table->foreign('file_id')->references('id')->on('code_map_files')->onDelete('cascade');
            $table->foreign('depends_on_file_id')->references('id')->on('code_map_files')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_map_dependencies');
    }
};
