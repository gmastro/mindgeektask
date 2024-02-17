<?php

/**
 * Class FeedFacade | ./app/Customizations/Facades/FeedFacade.php
 *
 * Feed Facade class captures downloaded content and uses specific convertor to seed storage medium.
 *
 * @package     Feeds
 * @subpackage  Customizations
 * @author      George Mastrovasilis <george.mastrovasilis@gmail.com>
 * @copyright   Copyright (c) 2023, George Mastrovasilis
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link        https://github.com/gmastro/mindgeektask/tree/master/app/Customizations/Facades/FeedFacade.php
 */

declare(strict_types=1);

namespace App\Customizations\Facades;

use App\Customizations\Components\interfaces\InterfaceContentTypes;
use App\Customizations\Components\AtomFeed;
use App\Customizations\Components\CsvFeed;
use App\Customizations\Components\HtmlFeed;
use App\Customizations\Components\JsonFeed;
use App\Customizations\Components\RdfFeed;
use App\Customizations\Components\RssFeed;
use App\Customizations\Components\XmlFeed;
use App\Customizations\Composites\DownloadComponent;
use App\Customizations\Proxies\interfaces\InterfaceFeed;

/**
 * Feed Facade
 *
 * Facade pattern performing a set of operations to prepare the data.
 *
 * @category    Facades
 * @package     Feeds
 * @version     0.0.1
 * @todo        Missing multilingual support for all available filetypes
 */
class FeedFacade
{
    /**
     * Converts Content
     *
     * This part behaves as a proxy, it will gather the information from the file and then convert it to the associative
     * handler object.
     *
     * @version 0.0.1
     * @since   0.0.1
     * @access  public
     * @param   DownloadComponent $component
     * @return  InterfaceFeed|null
     */
    public function convertor(DownloadComponent $component): ?InterfaceFeed
    {
        $share = $component->share();

        $extension = \explode('.', $share->filename);
        $extension = \end($extension);

        $contentTypes = InterfaceFeed::SUPPORTED[$extension] ?? [];

        if ($contentTypes === []) {
            return null;
        }

        $contentType = $contentTypes[$share->mime_type] ?? null;

        if ($contentType === null) {
            return null;
        }

        return match($contentType) {
            InterfaceContentTypes::APPLICATION_JSON                                 => new JsonFeed($share),
            InterfaceContentTypes::APPLICATION_ATOM_XML                             => new AtomFeed($share),
            InterfaceContentTypes::APPLICATION_RSS_XML                              => new RssFeed($share),
            InterfaceContentTypes::APPLICATION_RDF_XML                              => new RdfFeed($share),
            InterfaceContentTypes::TEXT_CSV                                         => new CsvFeed($share),
            InterfaceContentTypes::APPLICATION_XML, InterfaceContentTypes::TEXT_XML => new XmlFeed($share),
            InterfaceContentTypes::TEXT_HTML                                        => new HtmlFeed($share),
            default                                                                 => null,
        };
    }
}