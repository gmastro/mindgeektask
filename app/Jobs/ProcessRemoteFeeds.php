<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Customizations\Adapters\CurlDownloadAdapter;
use App\Customizations\Components\CurlComponent;
use App\Customizations\Composites\ExamineComponent;
use App\Events\RemoteFeedEvent;
use App\Jobs\Common\DownloadJob;
use App\Jobs\Common\ThumbnailsJob;
use App\Models\RemoteFeeds;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class ProcessRemoteFeeds implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    /**
     * Execute the job.
     *
     * Gets content from all those active feed sources
     */
    public function handle(): void
    {
        $batch = RemoteFeeds::all()
            ->reject(fn ($model): bool => $model->is_active === false || $model->chain->isEmpty())
            ->map(fn ($model) => $model->chain);

        // no jobs? bye!
        if($batch === []) {
            info("No jobs were found!");
            return;
        }
        
        Bus::batch($batch)
            ->allowFailures()
            ->onQueue('downloads')
            ->dispatch();


        // Bus::chain([
        //     fn() => Bus::batch($batch)
        //         ->allowFailures()
        //         ->name('downloading-feeds')
        //         ->onQueue('downloads')
        //         ->dispatch(),
        //     new ThumbnailsJob
        // ])
        // ->onQueue('downloads')
        // ->dispatch();
    }
}
