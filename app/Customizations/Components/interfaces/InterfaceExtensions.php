<?php

/**
 * Interface InterfaceExtensions | ./app/Customizations/Components/interfaces/InterfaceExtensions.php
 *
 * Contains file extension names
 *
 * @package     Feeds
 * @subpackage  Customizations
 * @author      George Mastrovasilis <george.mastrovasilis@gmail.com>
 * @copyright   Copyright (c) 2023, George Mastrovasilis
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link        https://github.com/gmastro/mindgeektask/tree/master/app/Customizations/Components/interfaces/InterfaceExtensions.php
 */

declare(strict_types=1);

namespace App\Customizations\Components\interfaces;

/**
 * Interface Extensions
 *
 * Contains standard naming extention conventions for all those acceptable to store filetypes.
 *
 * @category    Interfaces
 * @package     Components
 * @version     0.0.1
 * @todo        Add missing filetypes whenever required or needed.
 */
interface InterfaceExtensions
{
    /**
     * Extension Property
     *
     * Extension file type naming convention for rss feed files
     *
     * @access  public
     * @static
     * @var     string EXTENSION_RSS
     */
    public const EXTENSION_RSS = 'rss';

    /**
     * Extension Property
     *
     * Extension file type naming convention for atom feed files
     *
     * @access  public
     * @static
     * @var     string EXTENSION_ATOM
     */
    public const EXTENSION_ATOM = 'atom';

    /**
     * Extension Property
     *
     * Extension file type naming convention for atom feed files
     *
     * @access  public
     * @static
     * @var     string EXTENSION_RDF
     */
    public const EXTENSION_RDF = 'rdf';

    /**
     * Extension Property
     *
     * Extension file type naming convention for csv feed files
     *
     * @access  public
     * @static
     * @var     string EXTENSION_CSV
     */
    public const EXTENSION_CSV = 'csv';

    /**
     * Extension Property
     *
     * Extension file type naming convention for XML feed files
     *
     * @access  public
     * @static
     * @var     string EXTENSION_XML
     */
    public const EXTENSION_XML = 'xml';

    /**
     * Extension Property
     *
     * Extension file type naming convention for HTML files
     *
     * @access  public
     * @static
     * @var     string EXTENSION_HTML
     */
    public const EXTENSION_HTML = 'html';

    /**
     * Extension Property
     *
     * Extension file type naming convention for json feed files
     *
     * @access  public
     * @static
     * @var     string EXTENSION_JSON
     */
    public const EXTENSION_JSON = 'json';

    /**
     * Extension Property
     *
     * Extension file type for images
     *
     * @access  public
     * @static
     * @var     string EXTENSION_PNG
     */
    public const EXTENSION_PNG = 'png';

    /**
     * Extension Property
     *
     * Extension file type for images
     *
     * @access  public
     * @static
     * @var     string EXTENSION_JPG
     */
    public const EXTENSION_JPG = 'jpg';

    /**
     * Extension Property
     *
     * Extension file type for images
     *
     * @access  public
     * @static
     * @var     string EXTENSION_JPEG
     */
    public const EXTENSION_JPEG = 'jpeg';

    /**
     * Extension Property
     *
     * Extension file type for png images
     *
     * @access  public
     * @static
     * @var     string EXTENSION_GIF
     */
    public const EXTENSION_GIF = 'gif';
}
