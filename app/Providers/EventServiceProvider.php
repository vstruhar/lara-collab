<?php

namespace App\Providers;

use App\Events\Task\CommentCreated;
use App\Events\Task\TaskCreated;
use App\Events\UserCreated;
use App\Listeners\NotifyTaskSubscribers;
use App\Listeners\SendEmailWithCredentials;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserCreated::class => [
            SendEmailWithCredentials::class,
        ],
        TaskCreated::class => [
            NotifyTaskSubscribers::class,
        ],
        CommentCreated::class => [
            NotifyTaskSubscribers::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
