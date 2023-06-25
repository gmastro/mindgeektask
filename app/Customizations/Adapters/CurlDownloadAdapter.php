<?php
declare(strict_types=1);

namespace App\Customizations\Adapters;

use App\Customizations\Components\FileOpenComponent;
use App\Customizations\Components\interfaces\InterfaceErrorCodes;
use App\Customizations\Components\interfaces\InterfaceRemoteStream;
use App\Customizations\Components\interfaces\InterfaceStorage;
use App\Customizations\Traits\ErrorCodeTrait;
use App\Customizations\Traits\FilenameTrait;

/**
 * Downloads Content
 *
 * It will extract content from the source url.
 * The content will be stored into a file, within selected container.
 * This operation if for local storage only.
 */
class CurlDownloadAdapter implements InterfaceErrorCodes
{
    use ErrorCodeTrait;
    use FilenameTrait;

    /**
     * Magic Construct
     *
     * Opens a file stream handler and fetches content from remote source via curl.
     * The content is stored in binary format.
     *
     * @access  public
     * @param   InterfaceRemoteStream $remote Download handler
     * @param   InterfaceStorage $storage Determines how and where downloaded content will be stored
     * @param   string $disk One of the storage declared local disks.
     * @return  self
     */
    public function __construct(private InterfaceRemoteStream $remote, string $storagePath)
    {
        $this->setFilename(
            \is_dir($storagePath)
            ? \sprintf("%s/%s", $storagePath, $this->fromRemoteStream($remote))
            : $storagePath
        );
    }

    /**
     * Verify Download
     *
     * Checks if the file has been downloaded
     * At least that the file is generated and that it exists having the very same size in bytes.
     *
     * @access  public
     * @return  bool
     */
    public function isDownloaded(): bool
    {
        $filename = $this->getFilename();
        if($this->hasErrors() || \is_file($filename) === false) {
            return false;
        }

        $remoteFilesize = (int) $this->remote->getInfo()[$this->remote::CONTENT_LENGTH];
        $localFilesize = (int) \filesize($filename);
        return ($remoteFilesize === -1 && $localFilesize > 0)
            || ($remoteFilesize === $localFilesize);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): bool
    {
        $out = new FileOpenComponent($this->getFilename());
        $out->execute();
        if($this->setErrorCode($out->getErrorCode(), $out->hasErrors())) {
            return false;
        }

        $info = $this->remote->getInfo();
        $url  = $info[$this->remote::URL];
        $this->remote->setOptions([
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => false,
            CURLOPT_FILE            => $out->getHandle(),
        ]);

        $this->remote->execute();
        $this->setErrorCode($this->remote->getErrorCode(), $this->remote->hasErrors());

        $statusCode = (int) $info[$this->remote::STATUS_CODE];
        $this->setErrorCode(InterfaceErrorCodes::REMOTE_STATUS_CODE, $statusCode !== 200);

        $out->close();
        $this->setErrorCode($out->getErrorCode(), $out->hasErrors());

        return $this->isDownloaded();
    }
}
