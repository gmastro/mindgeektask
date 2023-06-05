<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\RemoteFeedDeleting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class RemoteFeedCleanupListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RemoteFeedDeleting $event): void
    {
        Storage::disk('downloads')->delete(\md5($event->feed->source));

        // get all images as md5 hash ids
        $thumbnails = $event->feed->thumbnails::select(['url'])->all()->map(
            fn($thumb): string => \md5($thumb->url)
        );

        Storage::disk('thumbnails')->delete($thumbnails);
        Redis::pipeline(function(Redis $pipe) use ($thumbnails) {
            \array_map(fn($thumb) => $pipe::del("key:$thumb"), $thumbnails);
        });
    }
}
