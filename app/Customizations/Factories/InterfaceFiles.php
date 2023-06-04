<?php
declare(strict_types=1);

namespace App\Customizations\Factories;

interface InterfaceFiles
{
    /**
     * Contents Getter
     *
     * Will return raw format of data stored within the file
     *
     * @access  public
     * @return  mixed
     */
    public function getContents(): mixed;

    /**
     * Path Getter
     *
     * Returns path of the file
     *
     * @access  public
     * @return  string
     * @todo    might be a good idea to set a flag for retrieving either absolute/relative path
     */
    public function getPath(): string;

    /**
     * Extension Getter
     *
     * Returns the extension of the given file.
     *
     * @access  public
     * @return  string
     */
    public function getExtension(): string;

    /**
     * File Exists
     *
     * Checks if the file exists within given filesystem
     *
     * @access  public
     * @return  bool
     */
    public function exists(): bool;

    /**
     * Remove File
     *
     * Removes a file from filesystem
     *
     * @access  public
     * @return  bool
     */
    public function unlink(): bool;
}