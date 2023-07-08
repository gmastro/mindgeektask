<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Customizations\Adapters\RedisFileCachingAdapter;
use App\Events\RemoteFeedDeleting;
use App\Models\DownloadedFiles;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class RemoteFeedDeletingListener implements ShouldQueue
{
    private function unset(?DownloadedFiles $model): void
    {
        if($model === null) {
            return;
        }

        $storage = Storage::disk($model->disk);
        $storage->delete($model->filename);
    }

    /**
     * Handle the event.
     */
    public function handle(RemoteFeedDeleting $event): void
    {
        $this->unset($event->model->downloaded);
        $collection = $event->model->downloaded_files;
        $collection->map(fn ($model) => $this->unset($model));
        Redis::pipeline(fn($pipe) => (new RedisFileCachingAdapter($pipe, $collection))->execute());
    }
}
