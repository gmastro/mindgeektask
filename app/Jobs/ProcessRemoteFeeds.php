<?php
declare(strict_types=1);

namespace App\Jobs;

use App\Customizations\Composites\ExamineComponent;
use App\Events\RemoteFeedEvent;
use App\Models\RemoteFeeds;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRemoteFeeds implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function uniqueId(): string
    {
        return __CLASS__;
    }

    /**
     * Execute the job.
     *
     * The following steps should happen in the following order
     * - Get all those sources defined within the database (in our case just one)
     * - Make sure that the source is active
     * - Examine the source
     * - Increment examination counter of the feed
     * - Update is_active flag in case that the link/source is invalid
     * - Get a timestamp comparison between database's last update and source's last modification.
     * - Proceed if last modified date is greater compared to the updated tuple value
     * - Perform a "clean dowload" of all active and outdated sources.
     *   As "clean download" implies, it will priorily remove the file from the filesystem, rather than replacing it.
     * - Increment download counter after successful retrieval of the source content
     * - Store sources into local disk 'downloads' named as an md5 hash of the source link (no-extension)
     * - Process stored file (use tuple `process_handler` to generate a new instance of the proccessing class)
     * - During the process:
     *     - map data into batches
     *     - remove data, files and cache from missing identifiers
     *     - upsert data (create/update)
     *     - create data for pivot tables after retrieving current content missing keys.
     * 
     * Examine
     */
    public function handle(): void
    {
        RemoteFeeds::all()
            ->reject(function ($feed): bool {
                return $feed->is_active
                    && (new ExamineComponent($feed))->execute();
            })
            ->map(function ($feed): void {
                RemoteFeedEvent::dispatch($feed::withoutRelations(), true);
            });
    }
}
