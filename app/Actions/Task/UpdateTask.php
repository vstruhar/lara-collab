<?php

namespace App\Actions\Task;

use App\Events\Task\TaskUpdated;
use App\Models\Task;

class UpdateTask
{
    public function update(Task $task, array $data): void
    {
        $updateField = key($data);

        if (! in_array($updateField, ['subscribed_users', 'labels'])) {
            $task->update($data);

            if ($updateField === 'group_id') {
                $task->update(['order_column' => 0]);
            }
        }

        if ($updateField === 'subscribed_users') {
            $task->subscribedUsers()->sync($data['subscribed_users']);
        }

        if ($updateField === 'labels') {
            $task->labels()->sync($data['labels']);
        }

        TaskUpdated::dispatch($task, $updateField);
    }
}
