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
     * No error at all
     *
     * @access  public
     * @static
     * @var     int NONE
     */
    public const NONE = 0;

    // 1..64 reserved from curl

    /**
     * Naming Convention
     *
     * Failed to read content from uri
     *
     * @access  public
     * @static
     * @var     int REMOTE_CONTENTS
     */
    public const REMOTE_CONTENTS = 1000;

    /**
     * Naming Convention
     *
     * Failed to read content from uri, invalid status code
     *
     * @access  public
     * @static
     * @var     int REMOTE_STATUS_CODE
     */
    public const REMOTE_STATUS_CODE = 1001;

    /**
     * Naming Convention
     *
     * Currently store entry up-to-date compared to the entry provided from the uri
     *
     * @access  public
     * @static
     * @var     int REMOTE_LAST_UPDATE
     */
    public const REMOTE_LAST_UPDATE = 1002;

    /**
     * Naming Convention
     *
     * Not acceptable content type
     *
     * @access  public
     * @static
     * @var     int REMOTE_CONTENT_TYPE
     */
    public const REMOTE_CONTENT_TYPE = 1003;

    /**
     * Naming Convention
     *
     * Failed to create a file descriptor
     *
     * @access  public
     * @static
     * @var     int FILE_OPEN
     */
    public const FILE_OPEN = 2001;

    /**
     * Naming Convention
     *
     * Failed to close the file descriptor
     *
     * @access  public
     * @static
     * @var     int FILE_CLOSE
     */
    public const FILE_CLOSE = 2002;

    /**
     * Naming Convention
     *
     * Provided file path is unreachable
     *
     * @access  public
     * @static
     * @var     int FILE_PATH
     */
    public const FILE_PATH = 2003;

    /**
     * Naming Convention
     *
     * Provided directory is unreachable.
     *
     * @access  public
     * @static
     * @var     int DIRECTORY_PATH
     */
    public const DIRECTORY_PATH = 2004;

    /**
     * Naming Convention
     *
     * Could not sanitize feed, invalid structure
     *
     * @access  public
     * @static
     * @var     int FEED_SOURCE
     */
    public const FEED_SOURCE = 3000;

    /**
     * Naming Convention
     *
     * Could not sanitize feed, invalid structure
     *
     * @access  public
     * @static
     * @var     int FEED_SANITIZATION
     */
    public const FEED_SANITIZATION = 3001;

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
