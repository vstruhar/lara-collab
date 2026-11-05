<?php

namespace App\Events\TaskGroup;

use App\Models\TaskGroup;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskGroupRestored implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<int, \App\Models\Task>  $tasks
     */
    public function __construct(
        public TaskGroup $taskGroup,
        public array $tasks = [],
        private bool $internal = false,
    ) {
        $this->dontBroadcastToCurrentUser();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $name = "App.Models.Project.{$this->taskGroup->project_id}";

        if ($this->internal) {
            $name .= '.internal';
        }

        return [
            new PrivateChannel($name),
        ];
    }
}
