<?php
declare(strict_types=1);

namespace App\Customizations\Components\interfaces;

use App\Customizations\Components\interfaces\InterfaceComponent;
use App\Customizations\Components\interfaces\InterfaceErrorCodes;

interface InterfaceStorage extends InterfaceComponent, InterfaceErrorCodes
{
    /**
     * Filename Setter
     *
     * Defines where the absolute or relative path for the given file
     *
     * @access  public
     * @param   string $filename
     * @return  void
     */
    public function setFilename(string $filename);

    /**
     * Handler Getter
     *
     * Resource file descriptor
     *
     * @access  public
     * @return  mixed
     */
    public function getHandle(): mixed;

    /**
     * Remove Resource
     *
     * It will close a file descriptor, either to release resources or terminate access
     *
     * @access  public
     * @return  void
     */
    public function close(): void;
}