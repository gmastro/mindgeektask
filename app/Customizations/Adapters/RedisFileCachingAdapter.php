<?php
declare(strict_types=1);

namespace App\Customizations\Adapters;

use App\Customizations\Adapters\interfaces\InterfaceAdapter;
use App\Models\DownloadedFiles;
use Illuminate\Database\Eloquent\Collection;
use Redis;

class RedisFileCachingAdapter implements InterfaceAdapter
{
    public function __construct(public Redis $pipe, private Collection $collection)
    {
        //
    }
    
    /**
     * Cache
     *
     * Will place a file into redis through redis pipe
     *
     * @access  private
     * @param   DownloadedFiles $model Current model through collection iterator
     * @return  Redis
     */
    private function cache(DownloadedFiles $model): Redis
    {
        $filename = \implode("/", [config("filesystems.disks")[$model->disk]['root'], $model->filename]);

        if(\is_file($filename) === false) {
            return false;
        }

        return $this->pipe->set("key:$model->md5_hash", \file_get_contents($filename));
    }

    /**
     * Unlink
     *
     * Removes file from redis cache, either the image is soft deleted and/or is not cached.
     *
     * @access  public
     * @param   array $paths Batch of keys to remove
     * @return  Redis
     */
    private function unlink(DownloadedFiles $model): Redis
    {
        return $this->pipe->del("key:$model->md5_hash");
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): bool
    {
        $this->collection->map(fn(DownloadedFiles $model) => $model->is_cached && $model->deleted_at === null
            ? $this->cache($model)
            : $this->unlink($model)
        );
        return true;
    }
}
