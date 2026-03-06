<?php

use App\Models\AttendanceSetting;
use App\Models\Company;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {

            AttendanceSetting::where('company_id', $company->id)
                ->where('qr_enable', 1)
                ->where('auto_clock_in', 'yes')
                ->update([
                    'auto_clock_in' => 'no',
                ]);

        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
