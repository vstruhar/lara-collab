<?php

namespace App\Listeners;

use App\Events\Task\CommentCreated;
use App\Events\Task\TaskCreated;
use App\Models\User;
use App\Notifications\CommentCreatedMentionedUserNotification;
use App\Notifications\CommentCreatedNotification;
use App\Notifications\TaskCreatedMentionedUserNotification;
use App\Notifications\TaskCreatedNotification;
use App\Services\UserMentionService;

class NotifyTaskSubscribers
{
    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        if ($event instanceof TaskCreated) {
            $this->handleCreatedTask($event);
        } elseif ($event instanceof CommentCreated) {
            $this->handleCreatedComment($event);
        }
    }

    protected function handleCreatedTask($event): void
    {
        $task = $event->task;
        $mentionedUsers = collect();

        // First handle notification for mentioned users
        if (UserMentionService::hasMentions($task->description)) {
            $mentionedUsers = UserMentionService::getUsersFromMentions($task->description, $task->project);

            $mentionedUsers->each(function (User $user) use ($task) {
                $user->notify(new TaskCreatedMentionedUserNotification($task));
            });
        }

        // Now handle subscribed users
        $task
            ->subscribedUsers
            ->reject(fn (User $user) => $user->id === auth()->id())
            ->reject(fn (User $user) => $mentionedUsers->contains('id', $user->id))
            ->each(function (User $user) use ($event) {
                $user->notify(new TaskCreatedNotification($event->task));
            });
    }

    protected function handleCreatedComment($event): void
    {
        $comment = $event->comment;
        $mentionedUsers = collect();

        // First handle notification for mentioned users
        if (UserMentionService::hasMentions($comment->content)) {
            $mentionedUsers = UserMentionService::getUsersFromMentions($comment->content, $comment->task->project);

            $mentionedUsers->each(function (User $user) use ($comment) {
                $user->notify(new CommentCreatedMentionedUserNotification($comment));
            });
        }

        // Now handle subscribed users
        $comment->task
            ->subscribedUsers
            ->reject(fn (User $user) => $user->id === auth()->id())
            ->reject(fn (User $user) => $mentionedUsers->contains('id', $user->id))
            ->each(function (User $user) use ($event) {
                $user->notify(new CommentCreatedNotification($event->comment));
            });
    }
}
