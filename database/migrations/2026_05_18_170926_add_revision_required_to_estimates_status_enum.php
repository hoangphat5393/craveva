<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE estimates MODIFY COLUMN status ENUM('declined','accepted','waiting','sent','draft','canceled','revision_required') NOT NULL DEFAULT 'waiting'");
    }

    public function down(): void
    {
        DB::table('estimates')
            ->where('status', 'revision_required')
            ->update(['status' => 'waiting']);

        DB::statement("ALTER TABLE estimates MODIFY COLUMN status ENUM('declined','accepted','waiting','sent','draft','canceled') NOT NULL DEFAULT 'waiting'");
    }
};
