<?php

namespace App\Listeners;

use App\Events\GroupEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GroupRelationListener implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(GroupEvent $event): array
    {
        // $from = $event->relation[0];
        // $to   = $event->relation[1];
        // $from->$to['name']()->$to['method']($to['models']);
        // return ['status' => $from->save(), 'value' => 43];
        Log::info('Listener handle', [
            'class'     => __CLASS__,
            'status'    => $event->status,
            'value'     => $event->value,
        ]);
        return ['status' => $event->status, 'value' => $event->value];
    }
}
