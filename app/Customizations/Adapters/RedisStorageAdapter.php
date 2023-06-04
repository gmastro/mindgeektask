<?php
declare(strict_types=1);

namespace App\Customizations\Adapters;

use App\Customizations\Factories\InterfaceDownload;
use App\Customizations\Factories\InterfaceStorage;
use App\Customizations\Factories\CurlDownload;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class RedisStorageAdapter implements InterfaceStorage, InterfaceDownload
{
    private $download;

    private $pipe;

    public function __construct(string $source, Redis $pipe)
    {
        $this->pipe = $pipe;
        $this->download = new CurlDownload($source, Storage::disk('thumbnails'));
    }
    
    /**
     * {@inheritdoc}
     */
    public function download(string $source = null): void
    {
        $this->download->download($source);
    }

    /**
     * {@inheritdoc}
     */
    public function store(): string|bool
    {
        $key = $this->download->getPath();
        $this->pipe->set("key:$key", $this->download->getContents());
        return true;
    }
}
