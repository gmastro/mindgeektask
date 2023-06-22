<?php
declare(strict_types=1);

namespace App\Customizations\Adapters;

use App\Customizations\Components\CurlComponent;
use App\Customizations\Components\FOpenComponent;
use InvalidArgumentException;
use UnhandledMatchError;

class DownloadAdapter
{
    /**
     * Adapter Property
     *
     * Uses curl component for our case to retrieve the content
     *
     * @access  private
     * @var     CurlComponent $adapter
     */
    private $adapter;

    /**
     * Disk Property
     *
     * Accepts local drivers.
     *
     * @access  private
     * @var     array $disk
     */
    private $disk;

    /**
     * Filename Property
     *
     * Holds the absolute path of the downloaded file.
     *
     * @access  private
     * @var     string|null $filename
     */
    private $filename;

    /**
     * Magic Construct
     *
     * Opens a file stream handler and fetches content from remote source via curl.
     * The content is stored in binary format.
     *
     * @access  public
     * @param   array $info Information about the path
     * @param   string $disk One of the storage declared local disks.
     * @return  self
     */
    public function __construct(array $info, string $disk = 'downloads')
    {
        $this->setDisk($disk);
        $this->filename = \sprintf("%s/%s", $this->disk['root'], $this->generateFilename($info));
        $stream = new FOpenComponent($this->filename);
        $this->adapter = new CurlComponent([
            CURLOPT_URL             => $info['url'],
            CURLOPT_FILE            => $stream->getHandler(),
            CURLOPT_RETURNTRANSFER  => false,
        ]);
        $this->adapter->run();
        unset($stream);

        if($this->isDownloaded() === false) {
            \unlink($this->filename);
        }
    }

    /**
     * Disk Setter
     *
     * Gets information related to used dis, usable for local storage only
     *
     * @access  public
     * @param   string $disk Name of disk located into `/storage/app/...`
     * @return  void
     * @todo    Needs to log and throw an exception in case that the disk does not exist
     *          I should figure out what kind of response this will return
     * @throws  InvalidArgumentException Works only with local drivers
     */
    private function setDisk(string $disk): void
    {
        $disk = config('filesystems.disks.' . $disk);

        if($disk['driver'] !== 'local') {
            throw new InvalidArgumentException("Download works only with local drivers");
        }

        $this->disk = $disk;
    }

   /**
     * Generate Filename
     *
     * It will generate a filename based on the following cases:
     * - when the path is the host name, it will add an extension
     * - it it matches the extension it will return the the path
     * - otherwise it will generate an md5 string of the url with captured extension
     *
     * @access  private
     * @param   array $info Header information from the file
     * @return  string
     * @throws  UnhandledMatchError when the content type is now amongst servicable cases
     */
    private function generateFilename(array $info): string
    {
        $contentType = \explode(';', $info['content_type'])[0];
        
        $extension = match($contentType) {
            'application/json'          => 'json',
            'text/xml'                  => 'xml',
            'text/html'                 => 'html',
            'image/png'                 => 'html',
            'image/jpg', 'image/jpeg'   => 'jpg',
            'image/gif'                 => 'gif',
            default                     => throw new UnhandledMatchError(\sprintf(
                "Not supported content type `%s` for download",
                $contentType
            )),
        };

        $filename = \explode('/', \parse_url($info['url'], PHP_URL_PATH));
        $filename = \end($filename);

        if(empty($filename)) {
            return \sprintf("%s.%s", \md5($info['url']), $extension);
        }

        $pattern = \sprintf("\%\.%s$\%", $extension);
        \preg_match($pattern, $filename, $matches);
        if($matches === []) {
            return \sprintf("%s.%s", $filename, $extension);
        }

        return $filename;
    }

    /**
     * Filename Getter
     *
     * Returns the absolute path of the file stored, this will be used for some extra processing
     *
     * @access  public
     * @return  string
     */
    public function getFilename(): ?string
    {
        return $this->isDownloaded()
            ? $this->filename
            : null;
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
        $info = $this->adapter->getInfo();
        return \is_array($info)
            && $this->adapter->getContents() === true
            && \intval($info['http_code']) === 200
            && \is_file($this->filename) === true
            && ((int) \filesize($this->filename)) === ((int) $info['download_content_length']);
    }
}
