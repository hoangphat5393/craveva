<?php

namespace Modules\Recruit\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class RecruitTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $companyId = 1;

        $this->call(SkillsTableSeeder::class, false, ['companyId' => $companyId]);
        $this->call(WorkExperienceTableSeeder::class, false, ['companyId' => $companyId]);
        $this->call(JobsTableSeeder::class, false, ['companyId' => $companyId]);
        $this->call(JobApplicationsTableSeeder::class, false, ['companyId' => $companyId]);
        $this->call(InterviewsTableSeeder::class, false, ['companyId' => $companyId]);
        $this->call(OfferLettersTableSeeder::class, false, ['companyId' => $companyId]);
        $this->call(CandidateTableSeeder::class, false, ['companyId' => $companyId]);
    }
}
