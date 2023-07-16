<?php

namespace App\Listeners;

use App\Events\RemoteFeedCreated;
use App\Jobs\Common\DownloadJob;
use App\Jobs\Common\ThumbnailsJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class RemoteFeedCreatedListener implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(RemoteFeedCreated $event)
    {
        $chain = [new DownloadJob($event->model)];

        if (\class_exists($event->model->handle)) {
            $class = $event->model->handle;
            $chain[] = new $class($event->model->id);
            $chain[] = new ThumbnailsJob($event->model->id);
        }

        Bus::chain($chain)->dispatch();
    }

    public function failed(RemoteFeedCreated $event, Throwable $e)
    {
        Log::error("Failed getting content for new feed", ['message' => $e->getMessage()]);
    }
}
