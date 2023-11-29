<?php

namespace App\Observers;

use App\Models\Project;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        $project->activities()->create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'title' => 'New project',
            'subtitle' => "\"{$project->name}\" was created by ".auth()->user()->name,
        ]);
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        if ($project->isDirty(['name'])) {
            $project->activities()->create([
                'project_id' => $project->id,
                'user_id' => auth()->id(),
                'title' => 'Project name was changed',
                'subtitle' => "from \"{$project->getOriginal('name')}\" to \"{$project->name}\" by ".auth()->user()->name,
            ]);
        }
    }

    /**
     * Handle the Project "archived" event.
     */
    public function archived(Project $project): void
    {
        $project->activities()->create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'title' => 'Project was archived',
            'subtitle' => "\"{$project->name}\" was archived by ".auth()->user()->name,
        ]);
    }

    /**
     * Handle the Project "unArchived" event.
     */
    public function unArchived(Project $project): void
    {
        $project->activities()->create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'title' => 'Project was unarchived',
            'subtitle' => "\"{$project->name}\" was unarchived by ".auth()->user()->name,
        ]);
    }
}
