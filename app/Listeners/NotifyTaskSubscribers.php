<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Events\TaskCreated;
use App\Models\User;
use App\Notifications\CommentCreatedNotification;
use App\Notifications\TaskCreatedNotification;

class NotifyTaskSubscribers
{
    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $event->task
            ->subscribedUsers
            ->reject(fn (User $user) => $user->id === auth()->id())
            ->each(function (User $user) use ($event) {
                if ($event instanceof TaskCreated) {
                    $user->notify(new TaskCreatedNotification($event->task));
                } elseif ($event instanceof CommentCreated) {
                    $user->notify(new CommentCreatedNotification($event->comment));
                }
            });
    }
}
