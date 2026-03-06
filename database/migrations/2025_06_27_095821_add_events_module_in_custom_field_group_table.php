<?php

use App\Models\Company;
use App\Models\CustomFieldGroup;
use App\Models\Event;
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
        Schema::table('custom_field_group', function (Blueprint $table) {
            $companies = Company::select('id')->get();
            $customFieldGroup = [];

            foreach ($companies as $company) {
                $customFieldGroup = [
                    [
                        'name' => 'Event',
                        'model' => Event::CUSTOM_FIELD_MODEL,
                        'company_id' => $company->id,
                    ],
                ];
            }

            CustomFieldGroup::insert($customFieldGroup);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_field_group', function (Blueprint $table) {
            //
        });
    }
};
