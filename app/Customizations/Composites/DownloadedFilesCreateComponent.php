<?php

declare(strict_types=1);

namespace App\Customizations\Composites;

use App\Customizations\Composites\interfaces\InterfaceShare;
use App\Customizations\Traits\ShareTrait;
use App\Models\DownloadedFiles;
use App\Models\RemoteFeeds;
use App\Models\Thumbnails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Store And Associate
 *
 * Once a file is downloaded it will attempt to store it and also attempt to associate it with
 * a model belonging to downloaded files
 */
class DownloadedFilesCreateComponent implements InterfaceShare
{
    use ShareTrait;

    /**
     * {@inheritdoc}
     */
    public function execute(): bool
    {
        try {
            DB::beginTransaction();
            $data = [
                'filename'  => $this->fetch('filename'),
                'disk'      => $this->fetch('disk'),
                'mime_type' => $this->fetch('mime_type'),
                'is_cached' => match ($this->fetch('mime_type')) {
                    'image/png', 'image/jpg', 'image/gif'   => true,
                    default                                 => false,
                },
            ];

            $downloadedFilesModel = DownloadedFiles::updateOrCreate($data, $data);

            if ($this->has('model') === true) {
                $model = &$this->fetch('model');
                $modelClass = \get_class($model);
                match ($modelClass) {
                    RemoteFeeds::class  => $model->download_counter++,
                    default             => null,
                };

                match ($modelClass) {
                    RemoteFeeds::class, Thumbnails::class => $model
                        ->downloaded()
                        ->associate($downloadedFilesModel)
                        ->save(),
                    default => null,
                };

                $this->transfer('model');
            }
            DB::commit();
        } catch(Throwable $e) {
            DB::rollBack();
            Log::error("Failed to upsert DownloadedFiles and/or create association", [
                'message'  => $e->getMessage(),
                'acquired' => $this->acquired
            ]);

            return false;
        }

        return true;
    }
}
