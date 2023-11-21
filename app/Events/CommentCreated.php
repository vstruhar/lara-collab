<?php

namespace App\Events;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Foundation\Events\Dispatchable;

class CommentCreated
{
    use Dispatchable;

    public Task $task;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Comment $comment,
    ) {
        $this->task = $comment->task;
    }
}
