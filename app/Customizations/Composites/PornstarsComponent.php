<?php

namespace App\Customizations\Composites;

use App\Customizations\Adapters\RedisStorageAdapter;
use App\Customizations\Composites\interfaces\InterfaceShare;
use App\Customizations\Factories\CurlDownload;
use App\Customizations\Traits\ShareTrait;
use App\Models\Pornstars;
use App\Models\PornstarsThumbnails;
use App\Models\RemoteFeeds;
use App\Models\Thumbnails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PornstarsComponent implements InterfaceShare
{
    use ShareTrait;

    private $map = [
        Pornstars::class            => [],
        Thumbnails::class           => [],
        PornstarsThumbnails::class  => [],
    ];

    private $keys = [
        'delete' => [],
        'upsert' => [],
    ];

    /**
     * Process Json
     *
     * Performing iterations per each and every item of the retrieved list and creating batches, used to
     * create, or update, or delete content from the DB.
     *
     * @access  private
     * @return  bool
     */
    private function processJson(): void
    {
        $downloadedFiles = $this->acquired->model->downloaded()->first();
        $filename = \implode("/", [config("filesystems.disks")[$downloadedFiles->disk]['root'], $downloadedFiles->filename]);
        $json = \json_decode(\file_get_contents($filename));

        foreach ($json->items as $item) {
            $thumbs = [];
            foreach ($item->thumbnails as $thumbnail) {
                $this->map[PornstarsThumbnails::class][$thumbnail->url] = [
                    'pornstar_id'   => $item->id,
                    'thumbnail_id'  => ''
                ];
                $thumbs[$thumbnail->url]['url']             = $thumbnail->url;
                $thumbs[$thumbnail->url]['remote_feed_id']  = $this->acquired->model->id;
                $thumbs[$thumbnail->url]['width']           = $thumbnail->width;
                $thumbs[$thumbnail->url]['height']          = $thumbnail->height;
                $thumbs[$thumbnail->url]['media'][]         = $thumbnail->type;
            }

            $this->map[Thumbnails::class] += $thumbs;
            $this->map[Pornstars::class][$item->id] = [
                'id'            => $item->id,
                'remote_feed_id'=> $this->acquired->model->id,
                'name'          => $item->name,
                'link'          => $item->link,
                'license'       => $item->license,
                'wl_status'     => (bool) $item->wlStatus,
                'attributes'    => $item->attributes,
                'stats'         => $item->attributes->stats ?? (object) [],
                'aliases'       => $item->aliases ?? [],
            ];
        }
    }

    /**
     * Delete Callback
     *
     * Will remove all tuples from relation after finding id and url differences on Pornstars and Thumbnails tables
     * respectively.
     * Those keys will be stored for extracting records for insert or create.
     * This is a batch operation.
     *
     * @access  private
     * @return  void
     */
    private function delete(): void
    {
        $keys = $this->acquired->model->pornstars->diffKeys(['id' => $this->map[Pornstars::class]])->keys()->toArray();
        Pornstars::where('id', $keys)->delete();
        $this->keys['delete'][Pornstars::class] = $keys;

        $keys = $this->acquired->model->thumbnails->diffKeys(['url' => $this->map[Thumbnails::class]])->keys()->toArray();
        Thumbnails::where('url', $keys)->delete();
        $this->keys['delete'][Thumbnails::class] = $keys;
    }

    /**
     * Insert Or Update Callback
     *
     * Preparing batch records for pornstars and thumbnails
     * Will only add data to the pivot table where lies the relation between pornstars and thumbnails
     *
     * @access  private
     * @return  void
     */
    private function upsert(): void
    {
        Pornstars::upsert(
            \array_intersect_key($this->map[Pornstars::class], \array_flip($this->keys['delete'][Pornstars::class])),
            'id',
            ['id', 'name', 'link', 'license', 'wl_status', 'attributes', 'stats', 'aliases', 'timestamps']
        );

        Thumbnails::upsert(
            \array_intersect_key($this->map[Thumbnails::class], \array_flip($this->keys['delete'][Thumbnails::class])),
            'url',
            ['url', 'width', 'height', 'media', 'timestamps']
        );
    }

    /**
     * Insert Callback
     *
     * Preparing a batch record for the data to insert
     * Will only add data to the pivot table where lies the relation between pornstars and thumbnails
     *
     * @access  private
     * @return  void
     */
    private function create(): void
    {
        foreach ($this->acquired->model->thumbnails()->get(['id', 'url']) as $item) {
            $this->map[PornstarsThumbnails::class][$item->url]['thumbnail_id'] = $item->id;
        }

        PornstarsThumbnails::upsert(
            $this->map[PornstarsThumbnails::class],
            ['pornstar_id', 'thumbnail_id'],
            ['pornstar_id', 'thumbnail_id']
        );
    }

    /**
     * {@inheritdoc}
     *
     * Steps
     * - Process the file
     * - Perform separate transactions to store content
     * - In case that a transaction fails, the exception will be capture by the composite class
     * - Finally increment the downloads and process counter.
     *
     * > **Note**:  This class will not download thumbnails. The download will take place into next step as next
     * >            Composite Leaf.
     *
     * @access  public
     * @return  bool
     */
    public function execute(): bool
    {
        $this->processJson();

        try {
            DB::beginTransaction();
            $this->delete();
            $this->upsert();
            $this->create();
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error("Failed to upsert DownloadedFiles and/or create association", [
                'message'  => $e->getMessage(),
                'acquired' => $this->acquired
            ]);
            return false;
        }

        $this->transfer('model')->append([
            'keys'  => $this->keys['delete'][Thumbnails::class],
            'disk'  => 'thumbnails',
        ]);

        return true;
        // $deletedKeys = $this->keys['delete'][Thumbnails::class];
        // $feedId = $this->feed->id;

        // Redis::pipeline(function(Redis $pipe) use ($feedId, $deletedKeys) {
        //     $redisStorage = new RedisStorageAdapter($pipe);

        //     // remove old thumbnails
        //     $paths = \array_map(fn($thumb) => \md5($thumb), $deletedKeys);
        //     $redisStorage->unlink($paths);
        //     $disk = Storage::disk('thumbnails');
        //     $disk->delete($paths);

        //     // add/refresh thumbnails
        //     $thumbs = Thumbnails::select('url')::all();
        //     foreach ($thumbs as $t) {
        //         $source = $t->url;
        //         $redisStorage->setDownload(new CurlDownload($source, $disk));
        //         $redisStorage->store($source);
        //     }
        // });

        // return $returnFlag;
    }
}
