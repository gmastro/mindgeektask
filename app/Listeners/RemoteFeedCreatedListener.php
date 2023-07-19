<?php

namespace App\Listeners;

use App\Events\RemoteFeedCreated;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class RemoteFeedCreatedListener
{
    /**
     * Handle the event.
     */
    public function handle(RemoteFeedCreated $event)
    {
        if ($event->model->chain->isEmpty()) {
            return false;
        }
        
        Bus::chain($event->model->chain)
            ->onQueue('downloads')
            ->dispatch();
    }

    public function failed(RemoteFeedCreated $event, Throwable $e)
    {
        Log::error("Failed getting content for feed", [
            'id'        => $event->model->id,
            'source'    => $event->model->source,
            'message'   => $e->getMessage()
        ]);
    }
}
