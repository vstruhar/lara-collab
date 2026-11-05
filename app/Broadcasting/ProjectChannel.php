<?php

namespace App\Broadcasting;

use App\Models\Task;
use Illuminate\Broadcasting\PrivateChannel;

class ProjectChannel
{
    public static function forTask(Task $task, bool $staffOnly = false): PrivateChannel
    {
        $name = "App.Models.Project.{$task->project_id}";

        if ($staffOnly || $task->hidden_from_clients) {
            $name .= '.internal';
        }

        return new PrivateChannel($name);
    }
}
