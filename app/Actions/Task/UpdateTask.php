<?php

namespace App\Actions\Task;

use App\Events\Task\TaskUpdated;
use App\Models\Task;

class UpdateTask
{
    public function update(Task $task, array $data): bool
    {
        $success = $task->update([
            'group_id' => $data['group_id'],
            'assigned_to_user_id' => $data['assigned_to_user_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'due_on' => $data['due_on'],
            'estimation' => $data['estimation'],
            'hidden_from_clients' => $data['hidden_from_clients'],
            'billable' => $data['billable'],
        ]);

        $task->subscribedUsers()->sync($data['subscribed_users']);

        $task->labels()->sync($data['labels']);

        TaskUpdated::dispatch($task);

        return $success;
    }
}
