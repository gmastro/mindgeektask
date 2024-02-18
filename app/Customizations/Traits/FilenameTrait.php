<?php

/**
 * Trait FilenameTrait | File ./src/Customizations/Traits/FilenameTrait.php
 *
 * Generates filenames from source.
 */

declare(strict_types=1);

namespace App\Customizations\Traits;

use App\Customizations\Components\interfaces\InterfaceRemoteStream;
use App\Customizations\Proxies\interfaces\InterfaceFeed;
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
     */
    protected function fromRemoteStream(InterfaceRemoteStream $stream): string
    {
        $info = $stream->getInfo();
        $contentType = \explode(';', $info[$stream::CONTENT_TYPE])[0];

        $extension = match ($contentType) {
            InterfaceFeed::APPLICATION_ATOM_XML                 => InterfaceFeed::EXTENSION_ATOM,
            InterfaceFeed::APPLICATION_JSON                     => InterfaceFeed::EXTENSION_JSON,
            InterfaceFeed::APPLICATION_RSS_XML                  => InterfaceFeed::EXTENSION_RSS,
            InterfaceFeed::APPLICATION_RDF_XML                  => InterfaceFeed::EXTENSION_RDF,
            InterfaceFeed::APPLICATION_XML                      => InterfaceFeed::EXTENSION_XML,
            InterfaceFeed::TEXT_XML                             => InterfaceFeed::EXTENSION_XML,
            InterfaceFeed::TEXT_HTML                            => InterfaceFeed::EXTENSION_HTML,
            InterfaceFeed::TEXT_CSV                             => InterfaceFeed::EXTENSION_CSV,
            InterfaceFeed::IMAGE_JPG, InterfaceFeed::IMAGE_JPEG => InterfaceFeed::EXTENSION_JPG,
            InterfaceFeed::IMAGE_PNG                            => InterfaceFeed::EXTENSION_PNG,
            InterfaceFeed::IMAGE_GIF                            => InterfaceFeed::EXTENSION_GIF,
            default                                             => throw new UnhandledMatchError(\sprintf(
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
