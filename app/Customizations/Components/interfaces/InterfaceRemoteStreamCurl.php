<?php
declare(strict_types=1);

namespace App\Customizations\Components\interfaces;

use App\Customizations\Components\interfaces\InterfaceRemoteStream;

/**
 * Curl Info Conventions
 *
 * Holds various naming conventions delivered via `curl_info`.
 * Required to extract from the associative array those values of interest using a unified format.
 */
interface InterfaceRemoteStreamCurl extends InterfaceRemoteStream
{
    public const CONTENT_LENGTH = 'download_content_length';
    public const CONTENT_TYPE = 'content_type';
    public const FILETIME = 'filetime';
    public const STATUS_CODE = 'http_code';
    public const URL = 'url';
}