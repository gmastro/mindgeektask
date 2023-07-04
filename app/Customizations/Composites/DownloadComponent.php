<?php

declare(strict_types=1);

namespace App\Customizations\Composites;

use App\Customizations\Adapters\CurlDownloadAdapter;
use App\Customizations\Composites\interfaces\InterfaceShare;
use App\Customizations\Traits\ShareTrait;
use DomainException;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use OutOfBoundsException;

/**
 * Performs Download
 *
 * Right after a source is examined it downloads and stores the content
 * For any failure either during download or storage it will throw an exception
 * Once the file is created it will return filename, mime-type and fullpath.
 */
class DownloadComponent implements InterfaceShare
{
    use ShareTrait;

    /**
     * {@inheritdoc}
     *
     * @throws  OutOfBoundsException it the disk has not been acquired via ShareTrait
     * @throws  InvalidArgumentException if the disk is not configured
     * @throws  DomainException if the storage process failed
     */
    public function execute(): bool
    {
        /**
         * @var     FilesystemManager $storage
         */
        $storage = Storage::disk($this->fetch('disk'));
        $path = $storage->path('');

        $examiner = $this->fetch('examine');
        $download = new CurlDownloadAdapter($examiner, $path);
        $response = $download->execute();

        if ($response === false) {
            throw new DomainException(\strtr("Terminated with the following errors: {errorCodes}", [
                '{errorCodes}' => \implode(", ", \array_keys($download->getErrorBatch()))
            ]));
        }

        $filename = \explode('/', $download->getFilename());
        $this->transfer('model')
            ->transfer('disk')
            ->append([
                'mime_type' => $examiner->getInfo()[$examiner::CONTENT_TYPE],
                'fullpath'  => $download->getFilename(),
                'filename'  => \end($filename),
            ]);

        return true;
    }
}
