<?php

namespace App\Providers;

use App\Events\ChirpCreated;
use App\Events\RemoteFeedDeleting;
use App\Events\RemoteFeedEvent;
use App\Listeners\RemoteFeedCleanupListener;
use App\Listeners\RemoteFeedListener;
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
        // remove
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ChirpCreated::class => [
            SendChirpCreatedNotifications::class,
        ],
        RemoteFeedDeleting::class => [
            RemoteFeedCleanupListener::class,
        ],
        RemoteFeedEvent::class => [
            RemoteFeedListener::class,
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
