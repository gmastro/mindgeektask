<?php

namespace App\Listeners;

use App\Customizations\Composites\DownloadComponent;
use App\Customizations\Composites\ExamineComponent;
use App\Customizations\Composites\PornstarsComponent;
use App\Customizations\Composites\RemoteFeedComposite;
use App\Events\RemoteFeedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class RemoteFeedListener implements ShouldQueue
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
    public function handle(RemoteFeedEvent $event): void
    {
        $composite = new RemoteFeedComposite(collect([]));
        
        if($event->isExamined === false) {
            $composite->push(new ExamineComponent($event->feed));
        }

        if($event->isDownloaded === false) {
            $composite->push(new DownloadComponent($event->feed));
        }

        if($event->isUpdated === false) {
            $component = match($event->feed->process_handler) {
                PornstarsComponent::class   => new PornstarsComponent($event->feed),
                // other process components here
                default                     => throw new \UnhandledMatchError("unkwown process handler"),
            };

            $composite->push($component);
        }

        $composite->execute();
    }
}
