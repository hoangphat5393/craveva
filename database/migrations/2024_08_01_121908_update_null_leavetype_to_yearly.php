<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::table('leave_types')->whereNull('leavetype')->update(['leavetype' => 'yearly']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
