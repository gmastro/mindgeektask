<?php

declare(strict_types=1);

namespace App\Jobs\Common;

use App\Customizations\Adapters\RedisFileCachingAdapter;
use App\Models\DownloadedFiles;
use App\Models\Thumbnails;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

class CacheJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 360;

    public $failOnTimeout = false;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    public function handle()
    {
        Redis::pipeline(fn($pipe) => (new RedisFileCachingAdapter($pipe, DownloadedFiles::all()))->execute());
    }

    public function failed(Throwable $e)
    {
        Log::error($e->getMessage());
    }
}
