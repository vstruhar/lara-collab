<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Project;
use Illuminate\Console\Command;

class PruneActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:prune-activities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will remove outdated activities for each project.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $skip = 100;

        Project::all()
            ->each(function (Project $project) use ($skip) {
                $count = Activity::where('project_id', $project->id)->count();

                if ($count > $skip) {
                    Activity::where('project_id', $project->id)
                        ->latest()
                        ->skip($skip)
                        ->take($count - $skip)
                        ->delete();
                }
            });
    }
}
