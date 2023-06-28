<?php

namespace App\Listeners;

use App\Customizations\Adapters\CurlDownloadAdapter;
use App\Events\DownloadEvent;
use App\Events\RedisCacheEvent;
use App\Models\DownloadedFiles;
use DomainException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class DownloadListener implements ShouldQueue
{
    // use Queueable;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handle(DownloadEvent $event)
    {
        $path = config('filesystems.disks')[$event->disk]['root'] ?? null;

        if($path === null) {
            throw new RuntimeException("Unknown disk: `$event->disk`. Please check config/filesystems.php file");
        }

        $download = new CurlDownloadAdapter($event->examine, $path);
        $response = $download->execute();

        if($response === false) {
            throw new DomainException(\strtr("Terminated with the following errors: {errorCodes}", [
                '{errorCodes}' => \implode(", ", \array_keys($download->getErrorBatch()))
            ]));
        }

        $info     = $event->examine->getInfo();
        $filename = \explode('/', $download->getFilename());
        DB::transaction(fn() => DownloadedFiles::updateOrCreate([
            'filename'  => \end($filename),
            'disk'      => $event->disk,
            'mime_type' => $info[$event->examine::CONTENT_TYPE],
            'is_cached' => match($info[$event->examine::CONTENT_TYPE]) {
                'image/png', 'image/jpg', 'image/gif'   => true,
                default                                 => false,
            }
        ]));

        RedisCacheEvent::dispatchIf($response, null);

        return $response;
    }

    public function failed(DownloadEvent $event, Throwable $e)
    {
        Log::error($e->getMessage(), [
            'disk'  => $event->disk,
            'info'  => $event->examine->getInfo(),
        ]);
    }
}
