<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Models\User;
use App\Notifications\TaskCreatedNotification;

class NotifyTaskSubscribers
{
    /**
     * Handle the event.
     */
    public function handle(TaskCreated $event): void
    {
        $event->task
            ->subscribedUsers
            ->reject(fn (User $user) => $user->id === auth()->id())
            ->each(function (User $user) use ($event) {
                $user->notify(new TaskCreatedNotification($event->task));
            });
    }
}
