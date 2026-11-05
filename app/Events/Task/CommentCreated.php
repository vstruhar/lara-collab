<?php

namespace App\Events\Task;

use App\Broadcasting\ProjectChannel;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Comment $comment;

    private Task $task;

    /**
     * Create a new event instance.
     */
    public function __construct(Comment $comment)
    {
        $this->task = $comment->task()->firstOrFail();
        $this->comment = $comment->load('user:id,name,avatar,job_title');

        $this->dontBroadcastToCurrentUser();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            ProjectChannel::forTask($this->task),
        ];
    }
}
