<?php

declare(strict_types=1);

namespace App\Customizations\Components;

use App\Customizations\Components\interfaces\InterfaceErrorCodes;
use App\Customizations\Components\interfaces\InterfaceStorage;
use App\Customizations\Traits\ErrorCodeTrait;

/**
 * Opens file onto
 */
class FileOpenComponent implements InterfaceStorage
{
    use ErrorCodeTrait;

    /**
     * File Handler
     *
     * Creates the file handler right after delivered path
     *
     * @access  private
     * @var     stream|false
     */
    private $handle = false;

    /**
     * Magic Construct
     *
     * Creates a file, or replaces and truncates the content of an existing one.
     *
     * @access  public
     * @param   string|null $filename Location to read/write from
     * @param   string $mode Flags to determine the type of access for the given resource
     * @return  self
     */
    public function __construct(private ?string $filename, private string $mode = "wb")
    {
        // nothing here
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
        $this->close();
    }

    /**
     * Handle Getter
     *
     * Returns the handle resource, if it sucessfully created with write and to store content
     *
     * @access public
     * @return  resource|false
     */
    public function getHandle(): mixed
    {
        return $this->handle;
    }

    /**
     * Filename Setter
     *
     * Set the filename which we will place content
     *
     * @access  public
     * @param   string $filename Either relative or absolute path
     * @return  void
     */
    public function setFilename(string $filename = null): void
    {
        $this->filename = $filename;
    }

    /**
     * Close Descriptor
     *
     * It will remove resource from memory and will update path info of the file.
     * Either use this method to release the resource, or wait until the destructor.
     *
     * @access  public
     * @return  void
     */
    public function close(): void
    {
        if (\is_resource($this->handle) === false) {
            return;
        }

        $this->setErrorCode(InterfaceErrorCodes::FILE_CLOSE, \fclose($this->handle) === false);
        if ($this->hasErrors() === false && $this->filename !== null) {
            \touch($this->filename);
        }
    }

    /**
     * {@inheritdoc}
     * Opens a file descriptor to store content.
     */
    public function execute(): bool
    {
        $this->setErrorCode(InterfaceErrorCodes::FILE_PATH, $this->filename === null);
        $this->setErrorCode(InterfaceErrorCodes::DIRECTORY_PATH, $this->filename !== null && \is_dir($this->filename));
        if ($this->hasErrors()) {
            return false;
        }

        $this->handle = \fopen($this->filename, $this->mode);
        $this->setErrorCode(InterfaceErrorCodes::FILE_OPEN, \is_resource($this->handle) === false);
        return $this->hasErrors() === false;
    }
}
