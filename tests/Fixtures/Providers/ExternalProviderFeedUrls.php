<?php

declare(strict_types=1);

namespace Tests\Fixtures\Providers;

use App\Customizations\Components\Feeds\AtomFeedComponent;
use App\Customizations\Components\Feeds\CsvFeedComponent;
use App\Customizations\Components\Feeds\JsonFeedComponent;
use App\Customizations\Components\Feeds\RdfFeedComponent;
use App\Customizations\Components\Feeds\RssFeedComponent;
use App\Customizations\Components\Feeds\XmlFeedComponent;

final class ExternalProviderFeedUrls
{
    /**
     * URL Feed Data Provider
     *
     * Contains various links for downloading feed content, for validation and processing
     *
     * @access  public
     * @static
     * @return  array<string, array<string, string>|string>
     */
    public static function providerSuccessUrls(): array
    {
        return [
            'sample-json'   => [
                [
                    'source'    => "https://freetestdata.com/wp-content/uploads/2023/04/1.05KB_JSON-File_FreeTestData.json"
                ],
                JsonFeedComponent::class,
            ],
            'sample-rdf'    => [
                [
                    'source'    => 'https://web.resource.org/rss/1.0/schema.rdf'
                ],
                XmlFeedComponent::class,
            ],
            'sample-atom'   => [
                [
                    'source'    => 'www.intertwingly.net/blog/index.atom'
                ],
                AtomFeedComponent::class,
            ],
            'sample-csv'    => [
                [
                    'source'    => 'https://cdn.wsform.com/wp-content/uploads/2020/06/industry.csv'
                ],
                CsvFeedComponent::class
            ],
        ];
    }

    /**
     * URL Feed Data Provider
     *
     * Contains links that may not be processed
     *
     * @access  public
     * @static
     * @return  array<string, array<string, string>>
     */
    public static function providerExceptionUrls(): array
    {
        return [
            'sample-png'    => [
                [
                    'source'    => "https://www.hamiltonstaracademy.com//images/frontpage/portfolio/fullsize/Screenshot1.png"
                ],
            ],
            'sample-html'   => [
                [
                    'source'    => "https://example.com"
                ],
            ],
        ];
    }
}