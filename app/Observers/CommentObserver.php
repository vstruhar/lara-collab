<?php

namespace App\Observers;

use App\Models\Comment;

class CommentObserver
{
    /**
     * Handle the Comment "created" event.
     */
    public function created(Comment $comment): void
    {
        $comment->activities()->create([
            'project_id' => $comment->task->project_id,
            'user_id' => auth()->id(),
            'title' => 'New comment',
            'subtitle' => auth()->user()->name." left a comment on \"{$comment->task->name}\" task",
        ]);
    }
}
