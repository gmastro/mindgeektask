<?php

namespace App\Listeners;

use App\Customizations\Adapters\RedisFileCachingAdapter;
use App\Events\RedisCacheEvent;
use App\Models\DownloadedFiles;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

class RedisCacheListener implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function uniqueId()
    {
        return __CLASS__;
    }

    public function handle(RedisCacheEvent $event)
    {
        if($event->feed === null) {
            $collection = DownloadedFiles::all();
        } else {
            $collection = $event->feed->downloaded_files();
        }

        return Redis::pipeline(fn($pipe) => (new RedisFileCachingAdapter($pipe, $collection))->execute());
    }

    public function failed(RedisCacheEvent $event, Throwable $e)
    {
        Log::error($e->getMessage(), [
            'remote-feed'    => $event->feed,
        ]);
    }
}
