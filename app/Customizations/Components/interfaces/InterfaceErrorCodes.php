<?php

declare(strict_types=1);

namespace App\Customizations\Components\interfaces;

/**
 * Holds Various error codes to identify various sources of caused problem
 * Used within customized classes to terminate content without throwing exceptions
 * or errors.
 * Any kind of throwable will be applied whenever required and after matching specific error codes.
 */
interface InterfaceErrorCodes
{
    /**
     * Naming Convention
     *
     * Content length in bytes from source
     *
     * @access  public
     * @static
     * @var     int CONTENT_LENGTH
     */
    public const NONE = 0;

    // 1..64 reserved from curl

    public const REMOTE_CONTENTS = 1000;

    public const REMOTE_STATUS_CODE = 1001;

    public const REMOTE_LAST_UPDATE = 1002;

    public const REMOTE_CONTENT_TYPE = 1003;

    public const FILE_OPEN = 2001;

    public const FILE_CLOSE = 2002;

    public const FILE_PATH = 2003;

    public const DIRECTORY_PATH = 2004;

    /**
     * Error Code Checker
     *
     * Returns true or false when an error has occured
     *
     * @access  public
     * @return  bool
     */
    public function hasErrors(): bool;

    /**
     * Error Code Getter
     *
     * Will return an error code to determine cases that something might have gone wrong
     *
     * @access  public
     * @return  int
     */
    public function getErrorCode(): int;
}
