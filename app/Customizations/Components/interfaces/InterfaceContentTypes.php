<?php

/**
 * Interface InterfaceFeed | ./app/Customizations/Proxies/interfaces/InterfaceFeed.php
 *
 * Contains content type naming conventions based on
 * {@link http://www.iana.org/assignments/media-types/media-types.xhtml} standards and
 * {@link https://www.iana.org/assignments/media-types-parameters/media-types-parameters.xhtml}
 *
 * @package     Feeds
 * @subpackage  Customizations
 * @author      George Mastrovasilis <george.mastrovasilis@gmail.com>
 * @copyright   Copyright (c) 2023, George Mastrovasilis
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link        https://github.com/gmastro/mindgeektask/tree/master/app/Customizations/Proxies/interfaces/InterfaceFeed.php
 */

declare(strict_types=1);

namespace App\Customizations\Components\interfaces;

/**
 * Interface Content Types
 *
 * Acceptable remote content types for reading and capturing their content.
 * Contains naming conventions only, for supported content extend this interface accordingly.
 *
 * @category    Interfaces
 * @package     Components
 * @version     0.0.1
 * @todo        Add any missing or needed content type.
 */
interface InterfaceContentTypes
{
    /**
     * Content Type Property
     *
     * Supported Content Type for feeds
     *
     * @access  public
     * @static
     * @var     string APPLICATION_RSS_XML
     */
    public const APPLICATION_RSS_XML = 'application/rss+xml';

    /**
     * Content Type Property
     *
     * Supported Content Type for feeds
     *
     * @access  public
     * @static
     * @var     string APPLICATION_ATOM_XML
     */
    public const APPLICATION_ATOM_XML = 'application/atom+xml';

    /**
     * Content Type Property
     *
     * Supported Content Type for feeds
     *
     * @access  public
     * @static
     * @var     string APPLICATION_RDF_XML
     */
    public const APPLICATION_RDF_XML = 'application/rdf+xml';

    /**
     * Content Type Property
     *
     * Supported Content Type for feeds
     *
     * @access  public
     * @static
     * @var     string APPLICATION_XML
     */
    public const APPLICATION_XML = 'application/xml';

    /**
     * Content Type Property
     *
     * Supported Content Type for feeds
     *
     * @access  public
     * @static
     * @var     string APPLICATION_JSON
     */
    public const APPLICATION_JSON = 'application/json';

    /**
     * Content Type Property
     *
     * Supported Content Type for feeds
     *
     * @access  public
     * @static
     * @var     string TEXT_XML
     */
    public const TEXT_XML = 'text/xml';

    /**
     * Content Type Property
     *
     * Supported Content Type for feeds
     *
     * @access  public
     * @static
     * @var     string TEXT_HTML
     */
    public const TEXT_HTML = 'text/html';

    /**
     * Content Type Property
     *
     * Supported Content Type for feeds
     *
     * @access  public
     * @static
     * @var     string TEXT_CSV
     */
    public const TEXT_CSV = 'text/csv';

    /**
     * Content Type Property
     *
     * Supported Content Type for images
     *
     * @access  public
     * @static
     * @var     string IMAGE_PNG
     */
    public const IMAGE_PNG = 'image/png';

    /**
     * Content Type Property
     *
     * Supported Content Type for images
     *
     * @access  public
     * @static
     * @var     string IMAGE_JPG
     */
    public const IMAGE_JPG = 'image/jpg';

    /**
     * Content Type Property
     *
     * Supported Content Type for images
     *
     * @access  public
     * @static
     * @var     string IMAGE_JPEG
     */
    public const IMAGE_JPEG = 'image/jpeg';

    /**
     * Content Type Property
     *
     * Supported Content Type for images
     *
     * @access  public
     * @static
     * @var     string IMAGE_PNG
     */
    public const IMAGE_GIF = 'image/gif';
}
