<?php

declare(strict_types=1);

namespace App\Jobs\Pornhub;

use App\Models\Pornstars;
use App\Models\PornstarsThumbnails;
use App\Models\RemoteFeeds;
use App\Models\Thumbnails;
use Carbon\Carbon;
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

class ProcessJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $model;

    private $json;

    private $chunks = 1000;

    public $timeout = 360;

    public $failOnTimeout = false;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    public function __construct()
    {
        $this->model = RemoteFeeds::find(1)->withoutRelations('pornstars', 'thumbnails', 'downloaded_files');
        // $this->onQueue('process');
        // $this->model->withoutRelations('pornstars', 'thumbnails', 'downloaded_files');
        // $downloadedFilesModel = $this->model->downloaded;
        // $filename = \implode("/", [config("filesystems.disks")[$downloadedFilesModel->disk]['root'], $downloadedFilesModel->filename]);
        $filename = \implode("/", [config("filesystems.disks.downloads.root"), "json_feed_pornstars.json"]);
        $this->json = \json_decode(\file_get_contents($filename));
    }

    public function handle(): void
    {
       $collection = [
            Pornstars::class            => [],
            Thumbnails::class           => [],
            PornstarsThumbnails::class  => [],
        ];

        foreach ($this->json->items as $item) {
            $thumbs = [];
            foreach ($item->thumbnails as $thumbnail) {
                foreach($thumbnail->urls as $url) {
                    $collection[PornstarsThumbnails::class][$url][] = $item->id;
                    $thumbs[$url]['url']             = $url;
                    $thumbs[$url]['remote_feed_id']  = $this->model->id;
                    $thumbs[$url]['width']           = $thumbnail->width;
                    $thumbs[$url]['height']          = $thumbnail->height;
                    $thumbs[$url]['media']           = \implode(
                        ',',
                        isset($thumbs[$url]['media']) ? [$thumbs[$url]['media'], $thumbnail->type] : [$thumbnail->type]
                    );
                    $thumbs[$url]['created_at']      = Carbon::now();
                    $thumbs[$url]['updated_at']      = Carbon::now();
                }
            }

            $collection[Thumbnails::class] += $thumbs;
            $attributes = $item->attributes;
            $stats = $attributes->stats ?? (object) [];
            unset($attributes->stats);
            $collection[Pornstars::class][$item->id] = [
                'id'            => $item->id,
                'remote_feed_id'=> $this->model->id,
                'name'          => $item->name,
                'link'          => $item->link,
                'license'       => $item->license,
                'wlStatus'      => (bool) $item->wlStatus,
                'attributes'    => \json_encode($attributes),
                'stats'         => \json_encode($stats),
                'aliases'       => \json_encode($item->aliases ?? []),
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ];
        }

        info("Upserting data for", [
            Pornstars::class    => \sizeof($collection[Pornstars::class]),
            Thumbnails::class   => \sizeof($collection[Thumbnails::class]),
        ]);

        $upsert = fn (Collection $data, string $table, string|array $uniqueFields, array $updateFields) => $data->map(
            fn (Collection $chunk) => DB::transaction(
                fn () => DB::table($table)->upsert($chunk->toArray(), $uniqueFields, $updateFields)
            )
        );
        
        $pornstars = Pornstars::whereNotIn('id', \array_keys($collection[Pornstars::class]));
        DB::transaction(fn () => $pornstars->delete());
        info("removed Pornstars", ['total' => $pornstars->count()]);

        $upsert(
            collect($collection[Pornstars::class])->chunk(1000),
            app(Pornstars::class)->getTable(),
            'id',
            ['id', 'name', 'link', 'license', 'wlStatus', 'attributes', 'stats', 'aliases', 'updated_at']
        );
        info("upserted Pornstars", ['total' => Pornstars::count()]);

        $thumbnails = Thumbnails::whereNotIn('url', \array_keys($collection[Thumbnails::class]));
        DB::transaction(fn () => $thumbnails->delete());
        info("removed Thumbnails", ['total' => $thumbnails->count()]);

        $upsert(
            collect($collection[Thumbnails::class])->chunk(1000),
            app(Thumbnails::class)->getTable(),
            'url',
            ['url', 'width', 'height', 'media', 'updated_at']
        );
        info("upserted Thumbnails", ['total' => Thumbnails::count()]);

        // not really worth it, way better to do an upsert by preparing the data
        Thumbnails::all()->each(
            fn ($model) => $model->pornstars()->sync($collection[PornstarsThumbnails::class][$model->url])
        );
        info("synced Pivot PornstarsThumbnails", ['total' => PornstarsThumbnails::count()]);
        // WAY A LOT DATA FOR A QUEUE TO HANDLE
        // Bus::batch([
        //     fn () => new UpsertJob(collect($collection[Pornstars::class])->chunk(1000), Pornstars::class),
        //     fn () => new UpsertJob(collect($collection[Thumbnails::class])->chunk(1000), Thumbnails::class),
        // ])
        // ->allowFailures()
        // ->catch(fn ($batch, Throwable $e) => Log::error($e->getMessage(), ['batch' => $batch]))
        // ->finally(fn ($batch) => info("finished batch jobs with", ['id' => $batch->id]))
        // ->name('upsert to db')
        // ->dispatch();
        
        // UpsertJob::dispatch($collection[Pornstars::class], Pornstars::class);
        // UpsertJob::dispatch($collection[Thumbnails::class], Thumbnails::class);

        // $collection[Pornstars::class] = collect($collection[Pornstars::class])->chunk($this->chunks);
        // $collection[Thumbnails::class] = collect($collection[Thumbnails::class])->chunk($this->chunks);
        // $collection[PornstarsThumbnails::class] = collect($collection[PornstarsThumbnails::class])->chunk($this->chunks);

        // $batchGenerator = fn (string $className, string|array $uniqueFields, array $updateFields) => $collection[$className]->map(
        //     fn (Collection $chunk) =>
        //         fn () => DB::transaction(
        //             fn () => DB::table(app($className)->getTable())->upsert($chunk->toArray(), $uniqueFields, $updateFields)
        //         )
        //     );

        // $batches = $collection[Pornstars::class]->map(fn (Collection $chunk) =>
        //     fn () => DB::transaction(
        //         fn () => DB::table('pornstars')->upsert(
        //             $chunk->toArray(),
        //             'id',
        //             ['id', 'name', 'link', 'license', 'wlStatus', 'attributes', 'stats', 'aliases', 'updated_at']
        //         )
        //     )
        // );

        // for batches with chains
        // $batch = [
        //     batchjob1,
        //     ...
        //     batchjobn, [
        //         chainjob1,
        //         ...
        //         chainjobn,
        //     ]
        // ]
        // 
        // or for chains with batches
        // $chain = [
        //     chainjob1,
        //     ...
        //     chainjobn,
        //     fn () => Bus::batch([batchjob1, ...])->dispatch(),
        // ]

        // Bus::batch($batchGenerator(Pornstars::class, 'id', ['id', 'name', 'link', 'license', 'wlStatus', 'attributes', 'stats', 'aliases', 'updated_at']))
        //     ->allowFailures()
        //     ->catch(fn ($batch, Throwable $e) => Log::error($e->getMessage(), ['batch' => $batch]))
        //     ->then(function ($batch) {
        //         info('a Pornstars batch has finished', ['id' => $batch->id, 'name' => $batch->name]);
        //     })
        //     ->finally(function ($batch) {
        //         info('all Pornstars batches have finished', ['id' => $batch->id, 'name' => $batch->name]);
        //     })
        //     ->name("Pornstars upsert")
        //     ->dispatch();

        // Bus::batch($batchGenerator(Thumbnails::class, 'url', ['url', 'width', 'height', 'media', 'updated_at']))
        //     ->allowFailures()
        //     ->catch(fn ($batch, Throwable $e) => Log::error($e->getMessage(), ['batch' => $batch]))
        //     ->then(function ($batch) {
        //         info('a thumbnails batch has finished', ['id' => $batch->id, 'name' => $batch->name]);
        //     })
        //     ->finally(function ($batch) {
        //         info('all thumbnails have finished', ['id' => $batch->id, 'name' => $batch->name]);
        //     })
        //     ->name("Thumbnails upsert")
        //     ->dispatch();
    }

    // public function failed(Throwable $e)
    // {
    //     Log::error($e->getMessage());
    // }
}
