<?php
declare(strict_types=1);

namespace App\Customizations\Components;

class CurlComponent
{
    /**
     * Options Property
     *
     * Set the configuration for retrieving content via curl.
     *
     * @access  private
     * @var     array $options
     */
    private $options = [];

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
    public function __construct(array $options = [])
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
     * Info Setter
     *
     * No required for all cases, still can provide details whether the request was a success or not
     *
     * @access  public
     * @param   null|false|array info Holds information related to the given site
     * @return  void
     */
    public function setInfo(mixed $info): void
    {
        $this->info = $info;
    }

    /**
     * Contents Setter
     *
     * Contents to store via `\curl_exec`
     *
     * @access  public
     * @param   mixed $contents
     * @return  void
     */
    public function setContents(mixed $contents): void
    {
        $this->contents = $contents;
    }

    /**
     * Info Getter
     *
     * Returns the set of information captured after visiting given url
     *
     * @access  public
     * @return  mixed
     */
    public function getInfo(): mixed
    {
        return $this->info;
    }

    /**
     * Contents Getter
     *
     * Returns the contents as captured from the given source/url. In case of an error default value is false
     *
     * @access  public
     * @return  mixed
     */
    public function getContents(): mixed
    {
        return $this->contents;
    }

    /**
     * Run
     *
     * Performs all the operations for sending a request towards the given source
     *
     * @access  public
     * @return  void
     */
    public function run(): void
    {
        $ch = \curl_init();

        \curl_setopt_array($ch, $this->options);
        $this->setContents(\curl_exec($ch));

        if($this->getContents() !== false && !\curl_errno($ch)) {
            $this->setInfo(\curl_getinfo($ch));
        }

        \curl_close($ch);
    }
}