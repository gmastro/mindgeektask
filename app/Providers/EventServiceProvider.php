<?php

namespace App\Providers;

use App\Events\ChirpCreated;
use App\Events\RemoteFeedDeleting;
use App\Events\RemoteFeedCreated;
use App\Listeners\RemoteFeedCreatedListener;
use App\Listeners\RemoteFeedDeletingListener;
use App\Listeners\SendChirpCreatedNotifications;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ChirpCreated::class => [
            SendChirpCreatedNotifications::class,
        ],
        RemoteFeedDeleting::class => [
            RemoteFeedDeletingListener::class,
        ],
        RemoteFeedCreated::class => [
            RemoteFeedCreatedListener::class,
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
