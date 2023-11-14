<?php

/**
 * Trait FilenameTrait | File ./src/Customizations/Traits/FilenameTrait.php
 *
 * Generates filenames from source.
 */

declare(strict_types=1);

namespace App\Customizations\Traits;

use App\Customizations\Components\interfaces\InterfaceRemoteStream;
use UnhandledMatchError;

/**
 * Filename Trait
 *
 * Will store various error codes in case that something went wrong
 */
trait FilenameTrait
{
    /**
     * Filename Property
     *
     * Holds various error codes to determine cases which either information retrieval or content retrieval failed
     *
     * @access  protected
     * @var     string $filename
     */
    protected $filename;

    /**
     * Generate Filename
     *
     * It will generate a filename based on the following cases:
     * - when the path is the host name, it will add an extension
     * - it it matches the extension it will return the the path
     * - otherwise it will generate an md5 string of the url with captured extension
     *
     * @access  private
     * @return  string
     * @throws  UnhandledMatchError when the content type is now amongst servicable cases
     * @todo    Move content types and file extensions as constants.
     * @todo    Map content types and file extensions as constant array
     */
    protected function fromRemoteStream(InterfaceRemoteStream $stream): string
    {
        $info = $stream->getInfo();
        $contentType = \explode(';', $info[$stream::CONTENT_TYPE])[0];

        $extension = match ($contentType) {
            'application/atom+xml'      => 'atom',
            'application/json'          => 'json',
            'application/rss+xml'       => 'rss',
            'application/rdf+xml'       => 'rdf',
            'application/xml'           => 'xml',
            'image/png'                 => 'png',
            'image/jpg', 'image/jpeg'   => 'jpg',
            'image/gif'                 => 'gif',
            'text/xml'                  => 'xml',
            'text/html'                 => 'html',
            'text/csv'                  => 'csv',
            default                     => throw new UnhandledMatchError(\sprintf(
                "Not supported content type `%s` for download",
                $contentType
            )),
        };

        $filename = \explode('/', \parse_url($info[$stream::URL], PHP_URL_PATH));
        $filename = \end($filename);

        if (empty($filename)) {
            return \sprintf("%s.%s", \md5($info[$stream::URL]), $extension);
        }

        $pattern = \sprintf("/\.%s$/", $extension);
        \preg_match($pattern, $filename, $matches);
        if ($matches === []) {
            return \sprintf("%s.%s", $filename, $extension);
        }

        return $filename;
    }

    /**
     * Filename Setter
     *
     * Stores the filename that will be used either for getting or storing contents
     *
     * @access  protected
     * @param   string $filename
     * @return  void
     */
    protected function setFilename(string $filename): void
    {
        $this->filename = $filename;
    }

    /**
     * Filename Getter
     *
     * Returns stored filename, or null in case that nothing has been set.
     *
     * @access  public
     * @return  null|string
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }
}
