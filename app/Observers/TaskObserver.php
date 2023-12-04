<?php

namespace App\Observers;

use App\Models\Task;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        $task->activities()->create([
            'project_id' => $task->project_id,
            'user_id' => auth()->id(),
            'title' => 'New task',
            'subtitle' => "\"{$task->name}\" was created by ".auth()->user()->name,
        ]);

        if ($task->assigned_to_user_id !== null) {
            $task->assigned_at = now();
            $task->saveQuietly();
        }
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        if ($task->isDirty('name')) {
            $task->activities()->create([
                'project_id' => $task->project_id,
                'user_id' => auth()->id(),
                'title' => 'Task name was changed',
                'subtitle' => "from \"{$task->getOriginal('name')}\" to \"{$task->name}\" by ".auth()->user()->name,
            ]);
        }
        if ($task->isDirty('description')) {
            $task->activities()->create([
                'project_id' => $task->project_id,
                'user_id' => auth()->id(),
                'title' => 'Task description was changed',
                'subtitle' => "on \"{$task->name}\" by ".auth()->user()->name,
            ]);
        }
        if ($task->isDirty('assigned_to_user_id')) {
            $task->activities()->create([
                'project_id' => $task->project_id,
                'user_id' => auth()->id(),
                'title' => $task->assigned_to_user_id ? 'Assigned user to task' : 'Assigned user was removed',
                'subtitle' => $task->assigned_to_user_id
                    ? "\"{$task->name}\" was assigned to {$task->assignedToUser->name} by ".auth()->user()->name
                    : "on task \"{$task->name}\" by ".auth()->user()->name,
            ]);

            $task->assigned_at = now();
            $task->saveQuietly();
        }
        if ($task->isDirty('due_on')) {
            $task->activities()->create([
                'project_id' => $task->project_id,
                'user_id' => auth()->id(),
                'title' => $task->due_on ? 'Due date was set on task' : 'Due date was removed',
                'subtitle' => $task->due_on
                    ? "to {$task->due_on->format('F j, Y')} on \"{$task->name}\" by ".auth()->user()->name
                    : "on \"{$task->name}\" task by ".auth()->user()->name,
            ]);
        }
        if ($task->isDirty('estimation')) {
            $task->activities()->create([
                'project_id' => $task->project_id,
                'user_id' => auth()->id(),
                'title' => 'Estimation was set',
                'subtitle' => "to {$task->estimation}h on \"{$task->name}\" by ".auth()->user()->name,
            ]);
        }
        if ($task->isDirty('completed_at')) {
            $task->activities()->create([
                'project_id' => $task->project_id,
                'user_id' => auth()->id(),
                'title' => $task->completed_at ? 'Task was completed' : 'Task was set to uncompleted',
                'subtitle' => "\"{$task->name}\" was set as ".($task->completed_at ? 'completed' : 'uncompleted').' by '.auth()->user()->name,
            ]);
        }
    }

    /**
     * Handle the Project "archived" event.
     */
    public function archived(Task $task): void
    {
        $task->activities()->create([
            'project_id' => $task->project_id,
            'user_id' => auth()->id(),
            'title' => 'Task was archived',
            'subtitle' => "\"{$task->name}\" was archived by ".auth()->user()->name,
        ]);
    }

    /**
     * Handle the Project "unArchived" event.
     */
    public function unArchived(Task $task): void
    {
        $task->activities()->create([
            'project_id' => $task->project_id,
            'user_id' => auth()->id(),
            'title' => 'Task was unarchived',
            'subtitle' => "\"{$task->name}\" was unarchived by ".auth()->user()->name,
        ]);
    }
}
