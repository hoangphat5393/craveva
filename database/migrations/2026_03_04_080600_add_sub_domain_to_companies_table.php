<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('companies', 'sub_domain')) {
            return;
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->string('sub_domain', 191)->nullable()->after('id');
        });
    }

    public function down(): void
    {
    }
};

