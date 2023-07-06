<?php

declare(strict_types=1);

namespace App\Customizations\Adapters;

use App\Customizations\Adapters\interfaces\InterfaceAdapter;
use App\Models\DownloadedFiles;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Storage;
use Redis;

class RedisFileCachingAdapter implements InterfaceAdapter
{
    /**
     * Magic Construct
     *
     * Uses redis pipeline and a `hard-dependency` of downloaded files collection
     * Depending upon conditions per downloaded file such as
     * - is_cached
     * - soft delete
     * The content of the file will be cached or be removed.
     *
     * @access  public
     * @param   mixed|Redis $pipe Pipeline generated via {@see Illuminate\Support\Facades\Redis}
     * @param   Collection<int, DownloadedFiles> $collection Files to cache/uncache via redis
     */
    public function __construct(public $pipe, private Collection $collection)
    {
        //
    }

    /**
     * Cache
     *
     * Will place a file into redis through redis pipe
     * > **Note**:  Each response will return `Redis` instance, which is currently undefined via data-type hint
     *
     * @access  private
     * @param   DownloadedFiles $model Current model through collection iterator
     * @return  mixed
     */
    private function cache(DownloadedFiles $model)
    {
        /**
         * @var FilesystemManager $storage
         */
        $storage = Storage::disk($model->disk);

        if ($storage->exists($model->filename) === false) {
            return $this->unlink($model);
        }

        return $this->pipe->set("key:$model->md5_hash", $storage->get($model->filename));
    }

    /**
     * Unlink
     *
     * Removes file from redis cache, either the image is soft deleted and/or is not cached.
     * > **Note**:  Each response will return `Redis` instance, which is currently undefined via data-type hint
     *
     * @access  public
     * @param   array $paths Batch of keys to remove
     * @return  mixed
     */
    private function unlink(DownloadedFiles $model)
    {
        return $this->pipe->del("key:$model->md5_hash");
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): bool
    {
        $this->collection->map(
            fn (DownloadedFiles $model) => $model->is_cached && $model->deleted_at === null
            ? $this->cache($model)
            : $this->unlink($model)
        );
        return true;
    }
}
