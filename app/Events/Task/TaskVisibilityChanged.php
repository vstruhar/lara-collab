<?php

namespace App\Events\Task;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskVisibilityChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(private Task $task)
    {
        $this->dontBroadcastToCurrentUser();
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.Models.Project.{$this->task->project_id}"),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        if ($this->task->hidden_from_clients) {
            return [
                'hidden' => true,
                'taskId' => $this->task->id,
            ];
        }

        return [
            'hidden' => false,
            'task' => $this->task->loadDefault(),
        ];
    }
}
