<?php
declare(strict_types=1);

namespace App\Customizations\Components;

use App\Customizations\Components\interfaces\InterfaceErrorCodes;
use App\Customizations\Components\interfaces\InterfaceRemoteStreamCurl;
use App\Customizations\Traits\ErrorCodeTrait;

/**
 * Curl Component
 *
 * Will return a curl result based on given options
 */
class CurlComponent implements InterfaceRemoteStreamCurl
{
    use ErrorCodeTrait;

    /**
     * Info Property
     *
     * Anything that get_headers will return
     *
     * @access  private
     * @var     null|false|iterable $info
     */
    private $info;

    /**
     * Contents Property
     *
     * Stores downloadable content from the given location
     *
     * @access  private
     * @var     mixed
     */
    private $contents;

    /**
     * Constructor
     */
    public function __construct(private array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * Options Setter
     *
     * Adds the range of options for retrieving content from the web or locally
     *
     * @access  public
     * @param   array An associative list of curl options for the request to perform
     * @return  void
     */
    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo(): mixed
    {
        return $this->info;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): mixed
    {
        return $this->contents;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): bool
    {
        $ch = \curl_init();

        \curl_setopt_array($ch, $this->options);
        $this->contents = \curl_exec($ch);

        $this->setErrorCode(\curl_errno($ch));
        $this->setErrorCode(InterfaceErrorCodes::REMOTE_CONTENTS, $this->getContents() === false);

        if($this->hasErrors() === false) {
            $this->info = \curl_getinfo($ch);
        }

        \curl_close($ch);
        return $this->hasErrors() === false;
    }
}