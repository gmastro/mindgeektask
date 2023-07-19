<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\RemoteFeeds;
use Illuminate\Bus\Batchable;
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
    use Batchable;

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
            ->map(fn ($model) => $model->chain->toArray());

        // no jobs? bye!
        if($batch === []) {
            info("No jobs were found!");
            return;
        }
        
        Bus::batch($batch)
            ->name('remote-feeds-scheduler')
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
