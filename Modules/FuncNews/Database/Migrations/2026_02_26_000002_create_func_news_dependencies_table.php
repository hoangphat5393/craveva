<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('func_news_dependencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_id');
            $table->unsignedBigInteger('depends_on_file_id');
            $table->string('relation_type')->nullable();
            $table->timestamps();

            $table->foreign('file_id')->references('id')->on('func_news_files')->onDelete('cascade');
            $table->foreign('depends_on_file_id')->references('id')->on('func_news_files')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('func_news_dependencies');
    }
};
