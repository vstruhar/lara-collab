<?php

namespace Database\Seeders;

use App\Models\ClientCompany;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [];

        $projects[] = Project::create([
            'name' => 'Demo Project',
            'description' => fake()->sentence(),
            'client_company_id' => ClientCompany::first()->id,
        ]);

        $projects[] = Project::create([
            'name' => 'Demo Project 2',
            'description' => fake()->sentence(),
            'client_company_id' => ClientCompany::oldest()->first()->id,
        ]);
    }
}
