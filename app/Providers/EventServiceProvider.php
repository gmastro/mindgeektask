<?php

namespace App\Providers;

use App\Events\ChirpCreated;
use App\Events\DownloadEvent;
use App\Events\ExamineEvent;
use App\Events\RedisCacheEvent;
use App\Events\RemoteFeedDeleting;
use App\Events\RemoteFeedEvent;
use App\Listeners\DownloadListener;
use App\Listeners\ExamineListener;
use App\Listeners\RedisCacheListener;
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
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ChirpCreated::class => [
            SendChirpCreatedNotifications::class,
        ],
        // some old events, still need to reconsider and put them in subscription instead?
        // some experemintation is still required
        RemoteFeedDeleting::class => [
            RemoteFeedCleanupListener::class,
        ],
        RemoteFeedEvent::class => [
            RemoteFeedListener::class,
        ],
        // and do your stuff from here
        ExamineEvent::class     => [
            ExamineListener::class
        ],
        DownloadEvent::class    => [
            DownloadListener::class,
        ],
        RedisCacheEvent::class  => [
            RedisCacheListener::class,
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
