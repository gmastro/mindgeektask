<?php
declare(strict_types=1);

namespace App\Customizations\Components;

use Illuminate\Support\Facades\Log;

/**
 * Opens file onto
 */
class FOpenComponent
{
    /**
     * Filename
     *
     * Holds either absolute/relative path for the file to open and write content from a resource
     *
     * @access  private
     * @var     string $filename
     */
    private $filename;

    /**
     * File Handler
     *
     * Creates the file handler right after delivered path
     *
     * @access  private
     * @var     stream|false
     */
    private $handler;

    /**
     * Magic Construct
     *
     * Creates a file, or replaces and truncates the content of an existing one.
     *
     * @access  public
     * @param   string $filename Either relative or absolute path
     * @return  self
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->handler = \fopen($this->filename, "wb");

        if($this->handler === false) {
            Log::error(\sprintf("Could not open\create file `%s` to store content", $this->filename));
        }
    }

    /**
     * Magic Destruct
     *
     * This is a must, it will release the handler and also will update information.
     *
     * @access  public
     */
    public function __destruct()
    {
        \fclose($this->handler);
        \touch($this->filename);
    }

    /**
     * Handler Getter
     *
     * Returns the handler to store content
     */
    public function getHandler(): mixed
    {
        return $this->handler;
    }
}