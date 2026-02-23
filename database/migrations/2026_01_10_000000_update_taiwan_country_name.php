<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('countries')
            ->where('iso', 'TW')
            ->update([
                'name' => 'TAIWAN',
                'nicename' => 'Taiwan'
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('countries')
            ->where('iso', 'TW')
            ->update([
                'name' => 'TAIWAN, PROVINCE OF CHINA',
                'nicename' => 'Taiwan, Province of China'
            ]);
    }
};
