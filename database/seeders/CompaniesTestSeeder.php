<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\EmployeeDetails;
use App\Models\Role;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Database\Seeder;

class CompaniesTestSeeder extends Seeder
{
    public function run(): void
    {
        config(['app.seeding' => true]);

        $faker = \Faker\Factory::create();

        $count = 3;

        foreach (range(1, $count) as $i) {
            $company = new Company();
            $company->company_name = $faker->company;
            $company->status = 'active';
            $company->locale = 'en';
            $company->save();

            $email = 'test.company' . $company->id . '@example.com';

            $userAuth = UserAuth::createUserAuthCredentials($email);

            $user = new User();
            $user->company_id = $company->id;
            $user->name = $faker->name;
            $user->email = $email;
            $user->status = 'active';
            $user->user_auth_id = $userAuth->id;
            $user->locale = $company->locale;
            $user->save();

            $adminRole = Role::withoutGlobalScope(\App\Scopes\CompanyScope::class)
                ->where('name', 'admin')
                ->where('company_id', $company->id)
                ->first();

            $employeeRole = Role::withoutGlobalScope(\App\Scopes\CompanyScope::class)
                ->where('name', 'employee')
                ->where('company_id', $company->id)
                ->first();

            if ($adminRole) {
                $user->roles()->attach($adminRole->id);
            }

            if ($employeeRole) {
                $user->roles()->attach($employeeRole->id);
            }

            $employee = new EmployeeDetails();
            $employee->user_id = $user->id;
            $employee->company_id = $company->id;
            $employee->employee_id = 'EMP-' . $user->id;
            $employee->save();

            $search = new UniversalSearch();
            $search->searchable_id = $user->id;
            $search->company_id = $company->id;
            $search->title = $user->name;
            $search->route_name = 'employees.show';
            $search->save();
        }

        config(['app.seeding' => false]);
    }
}

