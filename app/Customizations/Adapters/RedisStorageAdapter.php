<?php

declare(strict_types=1);

namespace App\Customizations\Adapters;

use App\Customizations\Factories\InterfaceDownload;
use App\Customizations\Factories\InterfaceStorage;
use App\Customizations\Factories\CurlDownload;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Redis;

class RedisStorageAdapter implements InterfaceStorage
{
    private $download;

    public function __construct(public Redis $pipe)
    {
        //
    }

    public function setDownload(InterfaceDownload $download): void
    {
        $this->download = $download;
    }

    /**
     * {@inheritdoc}
     */
    public function store(string $source): bool
    {
        $key = \md5($source);
        if ($this->download->exists() === false) {
            $this->download->download($source);

            if (($contents = $this->download->getContents()) === false) {
                return false;
            }
        } else {
            $contents = \file_get_contents($this->download->disk->get($key));
        }

        return $this->pipe::set("key:$key", $contents);
    }

    /**
     * Unlink
     *
     * Removes batch of keys from redis
     * As for files, well, will perform the operation directly after this method is called
     *
     * @access  public
     * @param   array $paths Batch of keys to remove
     * @return  void
     */
    public function unlink(array $paths): void
    {
        \array_map(fn ($key) => $this->pipe::del("key:$key"), $paths);
    }
}
