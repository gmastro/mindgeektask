<?php

namespace App\Listeners;

use App\Customizations\Components\CurlComponent;
use App\Events\DownloadEvent;
use App\Events\ExamineEvent;
use App\Events\RedisCacheEvent;
use DomainException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExamineListener implements ShouldQueue
{
    // use Queueable;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(ExamineEvent $event)
    {
        $examine = new CurlComponent([
            CURLOPT_URL             => $event->source,
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

        DownloadEvent::dispatchIf($response, $examine, $event->disk ?? 'thumbnails');

        // Bus::chain([
        //     new DownloadListener(new DownloadEvent($examine, $event->disk ?? 'thumbnails')),
        //     new RedisCacheListener(new RedisCacheEvent()),
        // ])->dispatch();

        return $response;
    }

    public function failed(ExamineEvent $event, Throwable $e)
    {
        Log::error($e->getMessage(), [
            'source'    => $event->source,
        ]);
    }
}
