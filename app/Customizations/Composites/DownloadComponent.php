<?php
declare(strict_types=1);

namespace App\Customizations\Composites;

use App\Customizations\Factories\CurlDownload;
use App\Customizations\Factories\FileGetContentsDownload;
use App\Models\RemoteFeeds;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DownloadComponent implements InterfaceComponent
{
    private $storage;

    /**
     * Magic Constructor
     *
     * Instanciate the dowload class to transfer required content within local the filesystem.
     * You may defined an alternative filesystem to store this file.
     *
     * @access  public
     * @param   RemoteFeeds $feed
     * @param   string $diskName Filesystem name to use
     */
    public function __construct(public RemoteFeeds $feed, string $diskName = 'downloads')
    {
        $this->storage = match($feed->download_handler) {
            CurlDownload::class             => new CurlDownload($feed->source, Storage::disk($diskName)),
            FileGetContentsDownload::class  => new FileGetContentsDownload($feed->source, Storage::disk($diskName)),
            // your other handlers,
            default                         => throw new \UnhandledMatchError("unkown dowload handler"),
        };
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): bool
    {
        $this->storage->unlink();
        $this->storage->download();
        if($this->storage->exists() === false) {
            return false;
        }

        return DB::transaction(fn(): bool => $this->feed->save([
            'download_counter' => $this->feed->download_counter + 1
        ]));
    }
}