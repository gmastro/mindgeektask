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
        $response = true;

        try {
            DB::beginTransaction();
            $downloadedFilesModel = DownloadedFiles::updateOrCreate([
                'filename'  => $this->acquired->filename,
                'disk'      => $this->acquired->disk,
                'mime_type' => $this->acquired->mime_type,
                'is_cached' => match ($this->acquired->mime_type) {
                    'image/png', 'image/jpg', 'image/gif'   => true,
                    default                                 => false,
                }
            ], [
                'filename'  => $this->acquired->filename,
                'disk'      => $this->acquired->disk,
                'mime_type' => $this->acquired->mime_type
            ]);

            if ($this->has('model') === true) {
                $modelClass = \get_class($this->acquired->model);
                $extras = match ($modelClass) {
                    RemoteFeeds::class  => ['download_counter' => $this->acquired->model->download_counter + 1],
                    default             => null,
                };

                match ($modelClass) {
                    RemoteFeeds::class, Thumbnails::class => $this->acquired->model
                        ->downloaded()
                        ->associate($downloadedFilesModel)
                        ->save($extras),
                    default => null,
                };
            }
            DB::commit();
        } catch(Throwable $e) {
            DB::rollBack();
            Log::error("Failed to upsert DownloadedFiles and/or create association", [
                'message'  => $e->getMessage(),
                'acquired' => $this->acquired
            ]);
            $response = false;
        }

        return $response;
    }
}
