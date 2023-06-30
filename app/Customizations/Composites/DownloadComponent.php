<?php

declare(strict_types=1);

namespace App\Customizations\Composites;

use App\Customizations\Adapters\CurlDownloadAdapter;
use App\Customizations\Composites\interfaces\InterfaceShare;
use App\Customizations\Traits\ShareTrait;
use DomainException;
use RuntimeException;

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
     */
    public function execute(): bool
    {
        $path = config('filesystems.disks')[$this->acquired->disk]['root'] ?? null;

        if ($path === null) {
            throw new RuntimeException("Unknown disk: `$this->acquired->disk`. Please check config/filesystems.php file");
        }

        $download = new CurlDownloadAdapter($this->acquired->examine, $path);
        $response = $download->execute();

        if ($response === false) {
            throw new DomainException(\strtr("Terminated with the following errors: {errorCodes}", [
                '{errorCodes}' => \implode(", ", \array_keys($download->getErrorBatch()))
            ]));
        }

        $filename = \explode('/', $download->getFilename());
        $this
            ->transfer('model')
            ->append([
                'mime_type' => $this->acquired->examine->getInfo()[$this->acquired->examine::CONTENT_TYPE],
                'disk'      => $this->acquired->disk,
                'fullpath'  => $filename,
                'filename'  => \end($filename),
            ]);

        return $response;
    }
}
