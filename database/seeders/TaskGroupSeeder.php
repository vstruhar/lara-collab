<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class TaskGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = Project::all();

        foreach ($projects as $project) {
            $project->taskGroups()->createMany([
                ['name' => 'Backlog'],
                ['name' => 'Todo'],
                ['name' => 'In progress'],
                ['name' => 'QA'],
                ['name' => 'Done'],
                ['name' => 'Deployed'],
            ]);
        }
    }
}
