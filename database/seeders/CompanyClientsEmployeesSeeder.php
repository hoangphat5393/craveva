<?php

namespace Database\Seeders;

use App\Models\ClientDetails;
use App\Models\Company;
use App\Models\EmployeeDetails;
use App\Models\Role;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Database\Seeder;

class CompanyClientsEmployeesSeeder extends Seeder
{
    public function run(): void
    {
        config(['app.seeding' => true]);

        $faker = \Faker\Factory::create();

        $companies = Company::all();

        foreach ($companies as $company) {
            $clientRole = Role::where('name', 'client')->where('company_id', $company->id)->first();
            $employeeRole = Role::where('name', 'employee')->where('company_id', $company->id)->first();

            // 5 clients per company
            foreach (range(1, 5) as $i) {
                $user = new User();
                $user->company_id = $company->id;
                $user->name = $faker->name;
                $user->email = $faker->unique()->safeEmail();
                $user->status = 'active';
                $user->locale = $company->locale;
                $user->save();

                $userAuth = UserAuth::create(['email' => $user->email, 'password' => bcrypt('123456')]);
                $user->user_auth_id = $userAuth->id;
                $user->saveQuietly();

                $client = new ClientDetails();
                $client->user_id = $user->id;
                $client->company_id = $company->id;
                $client->company_name = $faker->company;
                $client->address = $faker->address;
                $client->website = 'https://craveva.com';
                $client->save();

                if ($clientRole) {
                    $user->roles()->attach($clientRole->id);
                }

                $search = new UniversalSearch();
                $search->searchable_id = $user->id;
                $search->company_id = $company->id;
                $search->title = $user->name;
                $search->route_name = 'clients.show';
                $search->module_type = 'client';
                $search->save();
            }

            // 3 employees per company
            foreach (range(1, 3) as $i) {
                $user = new User();
                $user->company_id = $company->id;
                $user->name = $faker->name;
                $user->email = $faker->unique()->safeEmail();
                $user->status = 'active';
                $user->locale = $company->locale;
                $user->save();

                $userAuth = UserAuth::create(['email' => $user->email, 'password' => bcrypt('123456')]);
                $user->user_auth_id = $userAuth->id;
                $user->saveQuietly();

                $employee = new EmployeeDetails();
                $employee->user_id = $user->id;
                $employee->company_id = $company->id;
                $employee->employee_id = 'EMP-' . $user->id;
                $employee->save();

                if ($employeeRole) {
                    $user->roles()->attach($employeeRole->id);
                }

                $search = new UniversalSearch();
                $search->searchable_id = $user->id;
                $search->company_id = $company->id;
                $search->title = $user->name;
                $search->route_name = 'employees.show';
                $search->save();
            }
        }

        config(['app.seeding' => false]);
    }
}

