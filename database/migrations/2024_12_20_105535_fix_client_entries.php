<?php

use App\Models\EmployeeDetails;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // delete all client entries from employee_details table
        EmployeeDetails::withoutGlobalScopes()->join('client_details', 'employee_details.user_id', '=', 'client_details.user_id')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
