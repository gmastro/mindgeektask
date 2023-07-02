<?php

declare(strict_types=1);

namespace App\Jobs\Pornhub;

use App\Models\Pornstars;
use App\Models\PornstarsThumbnails;
use App\Models\RemoteFeeds;
use App\Models\Thumbnails;
use Carbon\Carbon;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use UnhandledMatchError;

class UpsertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public function __construct(private Collection $collection, private string $className)
    {
        //
    }

    public function handle(): void
    {
        info("Started upsert preparation with: ", [
            'on'    => $this->className,
            'table' => app($this->className)->getTable(),
            'chunks'=> $this->collection->count(),
        ]);

        $batchGenerator = fn (string|array $uniqueFields, array $updateFields) => $this->collection->map(
            fn (Collection $chunk) =>
                fn () => DB::transaction(
                    fn () => DB::table(app($this->className)->getTable())->upsert($chunk->toArray(), $uniqueFields, $updateFields)
                )
            );
        
        $fields = match($this->className) {
            Pornstars::class    => ['id',  ['id', 'name', 'link', 'license', 'wlStatus', 'attributes', 'stats', 'aliases', 'updated_at']],
            Thumbnails::class   => ['url', ['url', 'width', 'height', 'media', 'updated_at']],
            default             => new UnhandledMatchError('to be continued'),
        };

        Bus::batch($batchGenerator($fields[0], $fields[1]))
            ->allowFailures()
            ->catch(fn ($batch, Throwable $e) => Log::error($e->getMessage(), ['id' => $batch->id, 'name' => $batch->name]))
            ->finally(function ($batch) {
                info('all batches have finished', ['id' => $batch->id, 'name' => $batch->name]);
            })
            ->name("upsert with ". $this->className)
            ->dispatch();
    }

    // public function failed(Throwable $e)
    // {
    //     Log::error($e->getMessage());
    // }
}
