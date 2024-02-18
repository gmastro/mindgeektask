<?php

/**
 * Interface InterfaceFeed | ./app/Customizations/Proxies/interfaces/InterfaceFeed.php
 *
 * Declaring those services that will be used for a specific Proxy handler with specific lifecycle.
 *
 * @package     Feeds
 * @subpackage  Customizations
 * @author      George Mastrovasilis <george.mastrovasilis@gmail.com>
 * @copyright   Copyright (c) 2023, George Mastrovasilis
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link        https://github.com/gmastro/mindgeektask/tree/master/app/Customizations/Proxies/interfaces/InterfaceFeed.php
 */

declare(strict_types=1);

namespace App\Customizations\Proxies\interfaces;

use App\Customizations\Components\interfaces\InterfaceContentTypes;
use App\Customizations\Components\interfaces\InterfaceExtensions;

/**
 * Interface Feed
 *
 * Will include content processing for all those Feed 
 *
 * @category    Interfaces
 * @package     Feeds
 * @version     0.0.1
 * @todo        Add any extension and/or missing content types naming conventions and mappings within
 *              {@see InterfaceFeed::SUPPORTED}
 */
interface InterfaceFeed extends InterfaceProxy, InterfaceExtensions, InterfaceContentTypes
{
    /**
     * Field Names Appearence Flags
     *
     * Flag for all those attributes that appeared based on the given protocol
     *
     * @access  public
     * @static
     * @var     bool REQUIRED
     */
    public const REQUIRED = true;

    /**
     * Field Names Appearence Flags
     *
     * Flag for all those attributes that appeared based on the given protocol
     *
     * @access  public
     * @static
     * @var     bool OPTIONAL
     */
    public const OPTIONAL = null;

    /**
     * Field Names Appearence Flags
     *
     * Flag for all those attributes that appeared based on the given protoco.
     * Forbidden flag, also covers removed via deprication cases.
     *
     * @access  public
     * @static
     * @var     bool FORBIDDEN
     */
    public const FORBIDDEN = false;

    /**
     * Supported Extension To Content Type Mapping
     *
     * Holds supported relations of existing supersets(extensions) pointing towards related content types.
     *
     * @access  public
     * @static
     * @var     string[] SUPPORTED
     */
    public const SUPPORTED = [
        self::EXTENSION_XML     => [
            self::APPLICATION_ATOM_XML  => null,
            self::APPLICATION_RSS_XML   => null,
            self::APPLICATION_RDF_XML   => null,
            self::APPLICATION_XML       => null,
            self::TEXT_XML              => null,
        ],
        self::EXTENSION_RSS     => [
            self::APPLICATION_RSS_XML   => null,
            self::APPLICATION_XML       => null,
            self::TEXT_XML              => null,
        ],
        self::EXTENSION_ATOM    => [
            self::APPLICATION_ATOM_XML  => null,
            self::APPLICATION_XML       => null,
            self::TEXT_XML              => null,
        ],
        self::EXTENSION_RDF     => [
            self::APPLICATION_RSS_XML   => null,
            self::APPLICATION_XML       => null,
            self::TEXT_XML              => null,
        ],
        self::EXTENSION_CSV     => [
            self::TEXT_CSV              => null,
        ],
        self::EXTENSION_JSON    => [
            self::APPLICATION_JSON      => null,
        ],
    ];

    /**
     * 
     */
    public const PURE = [
        self::APPLICATION_ATOM_XML  => self::EXTENSION_ATOM,
        self::APPLICATION_JSON      => self::EXTENSION_JSON,
        self::APPLICATION_RDF_XML   => self::EXTENSION_RDF,
        self::APPLICATION_RSS_XML   => self::EXTENSION_RSS,
        self::APPLICATION_XML       => self::EXTENSION_XML,
        self::TEXT_CSV              => self::EXTENSION_CSV,
        self::TEXT_XML              => self::EXTENSION_XML,
    ];

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_VERSION
     */
    public const FIELD_VERSION = 'version';
    
    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_NAME
     */
    public const FIELD_NAME = 'name';
    
    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_URL
     */
    public const FIELD_URL = 'url';
    
    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_URI
     */
    public const FIELD_URI = 'uri';
    
    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_AVATAR
     */
    public const FIELD_AVATAR = 'avatar';
    
    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_HUBS
     */
    public const FIELD_HUBS = 'hubs';
    
    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_TYPE
     */
    public const FIELD_TYPE = 'type';
    
    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_MODE
     */
    public const FIELD_MODE = 'mode';
    
    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_TOPIC
     */
    public const FIELD_TOPIC = 'topic';
    
    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_REASON
     */
    public const FIELD_REASON = 'reason';
    
    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_ID
     */
    public const FIELD_ID = 'id';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_EXTERNAL_URL
     */
    public const FIELD_EXTERNAL_URL = 'external_url';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_TITLE
     */
    public const FIELD_TITLE = 'title';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_CONTENT_HTML
     */
    public const FIELD_CONTENT_HTML = 'content_html';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_CONTENT_TEXT
     */
    public const FIELD_CONTENT_TEXT = 'content_text';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_SUMMARY
     */
    public const FIELD_SUMMARY = 'summary';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_IMAGE
     */
    public const FIELD_IMAGE = 'image';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_BANNER_IMAGE
     */
    public const FIELD_BANNER_IMAGE = 'banner_image';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_DATE_PUBLISHED
     */
    public const FIELD_DATE_PUBLISHED = 'date_published';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_DATE_MODIFIED
     */
    public const FIELD_DATE_MODIFIED = 'date_modified';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_AUTHOR
     */
    public const FIELD_AUTHOR = 'author';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_AUTHORS
     */
    public const FIELD_AUTHORS = 'authors';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_TAGS
     */
    public const FIELD_TAGS = 'tags';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_LANGUAGE
     */
    public const FIELD_LANGUAGE = 'language';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_ATTACHMENTS
     */
    public const FIELD_ATTACHMENTS = 'attachments';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_HOME_PAGE_URL
     */
    public const FIELD_HOME_PAGE_URL = 'home_page_url';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_FEED_URL
     */
    public const FIELD_FEED_URL = 'feed_url';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_DESCRIPTION
     */
    public const FIELD_DESCRIPTION = 'description';
    
    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_USER_COMMENT
     */
    public const FIELD_USER_COMMENT = 'user_comment';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_NEXT_URL
     */
    public const FIELD_NEXT_URL = 'next_url';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_ICON
     */
    public const FIELD_ICON = 'icon';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_FAVICON
     */
    public const FIELD_FAVICON = 'favicon';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_EXPIRED
     */
    public const FIELD_EXPIRED = 'expired';

    /**
     * Field Property
     *
     * Common or Feed specific field name identifier
     *
     * @access  public
     * @static
     * @var     string FIELD_ITEMS
     */
    public const FIELD_ITEMS = 'items';
}
