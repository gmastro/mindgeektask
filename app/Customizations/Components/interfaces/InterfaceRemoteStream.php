<?php

declare(strict_types=1);

namespace App\Customizations\Components\interfaces;

use App\Customizations\Components\interfaces\InterfaceComponent;
use App\Customizations\Components\interfaces\InterfaceErrorCodes;

/**
 *
 */
interface InterfaceRemoteStream extends InterfaceComponent, InterfaceErrorCodes
{
    /**
     * Naming Convention
     *
     * Content length in bytes from source
     *
     * @access  public
     * @static
     * @var     string CONTENT_LENGTH
     */
    public const CONTENT_LENGTH = 'download_content_length';

    /**
     * Naming Convention
     *
     * Content type of source
     *
     * @access  public
     * @static
     * @var     string CONTENT_TYPE
     */
    public const CONTENT_TYPE = 'content_type';

    /**
     * Naming Convention
     *
     * When the given source was generated, this might be either unix timestamp or any other date time format
     *
     * @access  public
     * @static
     * @var     string FILETIME
     */
    public const FILETIME = 'filetime';

    /**
     * Naming Convention
     *
     * Response from the remote source
     *
     * @access  public
     * @static
     * @var     string STATUS CODE
     */
    public const STATUS_CODE = 'http_code';

    /**
     * Naming Convention
     *
     * Location of the remote source
     *
     * @access  public
     * @static
     * @var     string URL
     */
    public const URL = 'url';

    /**
     * Options Setter
     *
     * Will add those rules/options for remote content
     *
     * @access  public
     * @return  void
     */
    public function setOptions(array $options): void;

    /**
     * Information Getter
     *
     * Returns information from the remote source
     *
     * @access  public
     * @return  mixed
     */
    public function getInfo(): mixed;

    /**
     * Contents Getter
     *
     * Possible holds the contents or a success/failure flags from the remote source
     *
     * @access  public
     * @return  mixed
     */
    public function getContents(): mixed;
}
