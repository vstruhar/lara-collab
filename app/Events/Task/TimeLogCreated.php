<?php

namespace App\Events\Task;

use App\Models\Task;
use App\Models\TimeLog;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TimeLogCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public TimeLog $timeLog;

    /**
     * Create a new event instance.
     */
    public function __construct(
        private Task $task,
        TimeLog $timeLog,
    ) {
        $this->timeLog = $timeLog->load(['user:id,name']);

        $this->dontBroadcastToCurrentUser();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.Models.Project.{$this->task->project_id}"),
        ];
    }
}
