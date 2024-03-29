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
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcessJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Batchable;

    private $model;

    private $chunks = 1000;

    public $timeout = 360;

    public $failOnTimeout = false;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    public function __construct(private int $modelId)
    {
        //
    }

    public function handle(): void
    {
        $remoteFeedModel = RemoteFeeds::find($this->modelId)->withoutRelations('pornstars', 'thumbnails');

        /**
         * @var     FilesystemManager $storage
         **/
        $storage = Storage::disk($remoteFeedModel->downloaded->disk);
        $json = \json_decode($storage->get($remoteFeedModel->downloaded->filename));

        $collection = [
             Pornstars::class            => [],
             Thumbnails::class           => [],
             PornstarsThumbnails::class  => [],
         ];

        foreach ($json->items as $item) {
            $thumbs = [];
            foreach ($item->thumbnails as $thumbnail) {
                foreach($thumbnail->urls as $url) {
                    $collection[PornstarsThumbnails::class][$url][] = $item->id;
                    $thumbs[$url]['url']             = $url;
                    $thumbs[$url]['remote_feed_id']  = $remoteFeedModel->id;
                    $thumbs[$url]['width']           = $thumbnail->width;
                    $thumbs[$url]['height']          = $thumbnail->height;
                    $thumbs[$url]['media']           = \implode(
                        ',',
                        isset($thumbs[$url]['media']) ? [$thumbs[$url]['media'], $thumbnail->type] : [$thumbnail->type]
                    );
                    $thumbs[$url]['created_at']      = Carbon::now();
                }
            }

            $collection[Thumbnails::class] += $thumbs;
            $attributes = $item->attributes;
            $stats = $attributes->stats ?? (object) [];
            unset($attributes->stats);
            $collection[Pornstars::class][$item->id] = [
                'id'            => $item->id,
                'remote_feed_id'=> $remoteFeedModel->id,
                'name'          => $item->name,
                'link'          => $item->link,
                'license'       => $item->license,
                'wlStatus'      => (bool) $item->wlStatus,
                'attributes'    => \json_encode($attributes),
                'stats'         => \json_encode($stats),
                'aliases'       => \json_encode($item->aliases ?? []),
                'created_at'    => Carbon::now(),
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
            ['id', 'name', 'link', 'license', 'wlStatus', 'attributes', 'stats', 'aliases']
        );
        info("upserted Pornstars", ['total' => Pornstars::count()]);

        $thumbnails = Thumbnails::whereNotIn('url', \array_keys($collection[Thumbnails::class]));
        DB::transaction(fn () => $thumbnails->delete());
        info("removed Thumbnails", ['total' => $thumbnails->count()]);

        $upsert(
            collect($collection[Thumbnails::class])->chunk(1000),
            app(Thumbnails::class)->getTable(),
            'url',
            ['url', 'width', 'height', 'media']
        );
        info("upserted Thumbnails", ['total' => Thumbnails::count()]);

        // not really worth it, way better to do an upsert by preparing the data
        Thumbnails::all()->each(
            fn ($model) => $model->pornstars()->sync($collection[PornstarsThumbnails::class][$model->url])
        );
        info("synced Pivot PornstarsThumbnails", ['total' => PornstarsThumbnails::count()]);
    }
}
