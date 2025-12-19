<?php

namespace App\Observers;

use App\Models\TaskUser;

class TaskUserObserver
{

    public function created(TaskUser $taskUser): void
    {
        $taskUser->load('task', 'user');
        $task = $taskUser->task;
        $task->activities()->create([
            'project_id' => $task->project_id,
            'user_id' => auth()->id(),
            'title' => 'Assigned user to task',
            'subtitle' => "{$taskUser->user->name} was added to task \"{$task->name}\" by " . auth()->user()->name,
        ]);
        $task->assigned_at = now();
        $task->saveQuietly();
    }

    public function deleted(TaskUser $taskUser): void
    {
        $taskUser->load('task', 'user:id,name');
        $task = $taskUser->task;
        $task->activities()->create([
            'project_id' => $task->project_id,
            'user_id' => auth()->id(),
            'title' => 'Assigned user was removed',
            'subtitle' => "{$taskUser->user->name} was removed from task \"{$task->name}\" by " . auth()->user()->name,
        ]);
        $task->assigned_at = now();
        $task->saveQuietly();
    }
}