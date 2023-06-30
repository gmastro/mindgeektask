<?php

namespace App\Listeners;

use App\Customizations\Components\CurlComponent;
use App\Customizations\Composites\DownloadComponent;
use App\Customizations\Composites\ExamineComponent;
use App\Customizations\Composites\PornstarsComponent;
use App\Customizations\Composites\RemoteFeedComposite;
use App\Events\DownloadEvent;
use App\Events\RemoteFeedEvent;
use Carbon\Carbon;
use DomainException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Throwable;

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
    public function handle(RemoteFeedEvent $event)
    {
        $examine = new CurlComponent([
            CURLOPT_URL             => $event->feed->source,
            CURLOPT_NOBODY          => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FILETIME        => true,
        ]);

        $response = $examine->execute();

        if($response === false) {
            throw new DomainException(\strtr("Terminated with the following errors: {errorCodes}", [
                '{errorCodes}' => \implode(", ", \array_keys($examine->getErrorBatch()))
            ]));
        }

        $info = $examine->getInfo();
        $statusCode = (int) $info[$examine::STATUS_CODE];
        if($statusCode !== 200) {
            throw new DomainException("Terminated, expected status code 200, instead got $statusCode");
        }

        $filetime = Carbon::createFromTimestamp($info[$examine::REMOTE_LAST_UPDATE]);
        if($event->feed->updated_at->gte($filetime)) {
            throw new DomainException("Terminated, content is up to date");
        }

        Bus::chain([
            new DownloadEvent($examine, 'downloads'),
        ])->dispatch();

        return $response;
        // dd([
        //     'event'     => $event,
        // ]);
        // return;


        // $composite = new RemoteFeedComposite(collect([]));
        
        // if($event->isExamined === false) {
        //     $composite->push(new ExamineComponent($event->feed));
        // }

        // if($event->isDownloaded === false) {
        //     $composite->push(new DownloadComponent($event->feed));
        // }

        // if($event->isUpdated === false) {
        //     $component = match($event->feed->process_handler) {
        //         PornstarsComponent::class   => new PornstarsComponent($event->feed),
        //         // other process components here
        //         default                     => throw new \UnhandledMatchError("unkwown process handler"),
        //     };

        //     $composite->push($component);
        // }

        // $composite->execute();
    }

    public function failed(RemoteFeedEvent $event, Throwable $e)
    {
        DB::transaction(fn() => $event->feed->save([
            'examine_counter'   => $event->feed->examine_counter + 1,
            'timestamps'        => false,
        ]));
    }
}
