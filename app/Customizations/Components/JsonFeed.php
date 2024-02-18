<?php

/**
 * Class JsonFeed | ./app/Customizations/Components/JsonFeed.php
 *
 * Prepares the content for storage or in case of exclusive defined structure returns the content and process it as is.
 *
 * @package     Feeds
 * @subpackage  Customizations
 * @author      George Mastrovasilis <george.mastrovasilis@gmail.com>
 * @copyright   Copyright (c) 2023, George Mastrovasilis
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link        https://github.com/gmastro/mindgeektask/tree/master/app/Customizations/Components/JsonFeed.php
 */

declare(strict_types=1);

namespace App\Customizations\Components;

use App\Customizations\Proxies\interfaces\InterfaceFeed;
use Illuminate\Support\Facades\Storage;

/**
 * JSON Feed
 *
 * From downloaded reads and restructures the content based on {@link https://www.jsonfeed.org/version/x.x/}
 *
 * @category    Components
 * @package     Feeds
 * @version     0.0.1
 */
class JsonFeed implements InterfaceFeed
{
    /**
     * Authors Property
     *
     * This is an optional property. In case it exists it will be used to validate all author information
     *
     * @access  public
     * @static
     * @since   1.0
     * @var     array AUTHORS
     */
    public const AUTHORS = [
        self::FIELD_NAME    => [self::OPTIONAL, 'string'],
        self::FIELD_URL     => [self::OPTIONAL, 'string'],
        self::FIELD_AVATAR  => [self::OPTIONAL, 'string'],
    ];

    /**
     * HUBS Property
     *
     * Hubs will send feed content to a predefined endpoint for subscribed users only.
     * > **Note**:  The content here focuses on the properties related to feed request/response content and
     *              **SHOULD NOT** reference anything related to un/subscription.
     *
     * @access  public
     * @static
     * @since   1.1
     * @var     array HUBS
     * @todo    Based on Since hubs depend on the type should use different specifications once the type is defined
     * @todo    Each type holds different property set
     */
    public const HUBS = [
        self::FIELD_TYPE    => [self::REQUIRED, 'string', null, ["WebSub", "rssCloud"]],
        // websub
        self::FIELD_MODE    => [self::REQUIRED, 'string', null, ['subscribe', 'unsubscribe']],
        self::FIELD_MODE    => [self::REQUIRED, 'string'],
        self::FIELD_REASON  => [self::OPTIONAL, 'string'],
    ];

    public const ATTACHEMENTS = [

    ];

    /**
     * ITEMS Property
     *
     * All the feed content to store and represent depending upon account preferences.
     *
     * @access  public
     * @static
     * @since   1.0
     * @var     array ITEMS
     * @todo    Account preferences is something to declare per user and for future reference.
     */
    public const ITEMS = [
        self::FIELD_ID              => [self::REQUIRED, 'string'],
        self::FIELD_URL             => [self::OPTIONAL, 'string'],
        self::FIELD_EXTERNAL_URL    => [self::OPTIONAL, 'string'],
        self::FIELD_TITLE           => [self::OPTIONAL, 'string'],
        self::FIELD_CONTENT_TEXT    => [self::OPTIONAL, 'string'], // one of those 2 must be present, or both
        self::FIELD_CONTENT_HTML    => [self::OPTIONAL, 'string'], // one of those 2 must be present, or both
        self::FIELD_SUMMARY         => [self::OPTIONAL, 'string'],
        self::FIELD_IMAGE           => [self::OPTIONAL, 'string'],
        self::FIELD_BANNER_IMAGE    => [self::OPTIONAL, 'string'],
        self::FIELD_DATE_PUBLISHED  => [self::OPTIONAL, 'string'],
        self::FIELD_DATE_MODIFIED   => [self::OPTIONAL, 'string'],
        self::FIELD_AUTHORS         => [self::OPTIONAL, 'object[]', self::AUTHORS],
        self::FIELD_TAGS            => [self::OPTIONAL, 'string[]'],
        self::FIELD_LANGUAGE        => [self::OPTIONAL, 'string'],
        self::FIELD_ATTACHMENTS     => [self::OPTIONAL, 'object[]', self::ATTACHEMENTS],
    ];

    /**
     * Structure property
     *
     * It will hold default property rules. Those rules vary depending upon the version.
     *
     * @access  public
     * @static
     * @since   1.0
     * @var     array STRUCTURE
     * @todo    Current structure with state, data type, extension and constraints and default values **SHOULD** change
     *          in order to make sense.
     * @todo    The processes of validation could be part of a model utilizing the framework strucutre in order to
     *          validate and verify the content.
     */
    public const STRUCTURE = [
        self::FIELD_VERSION         => [self::REQUIRED, 'string'],
        self::FIELD_TITLE           => [self::REQUIRED, 'string'],
        self::FIELD_HOME_PAGE_URL   => [self::OPTIONAL, 'string'],
        self::FIELD_FEED_URL        => [self::OPTIONAL, 'string'],
        self::FIELD_DESCRIPTION     => [self::OPTIONAL, 'string'],
        self::FIELD_USER_COMMENT    => [self::OPTIONAL, 'string'],
        self::FIELD_NEXT_URL        => [self::OPTIONAL, 'string'],
        self::FIELD_ICON            => [self::OPTIONAL, 'string'],
        self::FIELD_FAVICON         => [self::OPTIONAL, 'string'],
        self::FIELD_LANGUAGE        => [self::OPTIONAL, 'string'],
        self::FIELD_EXPIRED         => [self::OPTIONAL, 'bool'],
        self::FIELD_HUBS            => [self::OPTIONAL, 'object[]', self::HUBS],
        self::FIELD_ITEMS           => [self::REQUIRED, 'object[]', self::ITEMS]
    ];

    public const STRUCTURE_1_0 = [
        self::FIELD_AUTHOR  => [self::OPTIONAL, 'object', self::AUTHORS],
    ];

    public const STRUCTURE_1_1 = [
        self::FIELD_AUTHORS => [self::OPTIONAL, 'object[]', self::AUTHORS],
    ];

    /**
     * Versions
     *
     * Holds active versions and those properties which are required or optional
     * Some of the properties are extended for specific structure validations.
     *
     * @access  public
     * @static
     * @var     array VERSIONS
     */
    public const VERSIONS = [
        "https://www.jsonfeed.org/version/1.1/" => [
            self::FIELD_VERSION                     => [self::REQUIRED, 'string'],
            self::FIELD_TITLE                       => [self::REQUIRED, 'string'],
            self::FIELD_HOME_PAGE_URL               => [self::OPTIONAL, 'string'],
            self::FIELD_FEED_URL                    => [self::OPTIONAL, 'string'],
            self::FIELD_DESCRIPTION                 => [self::OPTIONAL, 'string'],
            self::FIELD_USER_COMMENT                => [self::OPTIONAL, 'string'],
            self::FIELD_NEXT_URL                    => [self::OPTIONAL, 'string'],
            self::FIELD_ICON                        => [self::OPTIONAL, 'string'],
            self::FIELD_FAVICON                     => [self::OPTIONAL, 'string'],
            self::FIELD_AUTHORS                     => [self::OPTIONAL, 'object[]', self::AUTHORS],
            self::FIELD_LANGUAGE                    => [self::OPTIONAL, 'string'],
            self::FIELD_EXPIRED                     => [self::OPTIONAL, 'bool'],

        ],
    ];


    private string $version;

    private bool $isValid = false;

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
    public function __construct(private object $json)
    {
        $this->setVersion();
    }

    private function setVersion(): void
    {
        $version = $this->json->version ?? null;
        if((self::VERSIONS[$version] ?? false) === true) {
            $this->version = $version;
            $this->isValid = true;
        }
    }

    // private function setTitle(): void
    // {
    //     $this->tit
    // }
}
