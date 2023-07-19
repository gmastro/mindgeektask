<?php

declare(strict_types=1);

namespace App\Jobs\Common;

use App\Models\Thumbnails;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Throwable;

class ThumbnailsJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Batchable;

    public $timeout = 360;

    public $failOnTimeout = false;

    public function uniqueId(): string
    {
        return \implode(":", [__CLASS__, $this->remoteFeedId ?: "all"]);
    }

    public function __construct(private ?int $remoteFeedId = null)
    {
        // nothing here
    }

    public function handle()
    {
        if ($this->remoteFeedId !== null) {
            $collection = Thumbnails::where('remote_feed_id', $this->remoteFeedId)->get();
        } else {
            $collection = Thumbnails::all();
        }

        $batch = $collection->map(fn ($model) => new DownloadJob($model, 'thumbnails', $model->url));

        if ($batch === []) {
            return;
        }

        Bus::chain([
                fn() => Bus::batch($batch)
                    ->name('thumbnails-batch')
                    ->allowFailures()
                    ->onQueue('downloads')
                    ->dispatch(),
                new CacheJob,
            ])
            ->onQueue('downloads')
            ->dispatch();
    }

    public function failed(Throwable $e)
    {
        Log::error($e->getMessage());
    }
}
