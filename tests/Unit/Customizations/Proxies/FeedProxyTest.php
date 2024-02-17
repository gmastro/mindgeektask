<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Proxies;

use App\Customizations\Components\AtomFeed;
use App\Customizations\Components\CsvFeed;
use App\Customizations\Proxies\FeedProxy;
use App\Customizations\Components\FileOpenComponent;
use App\Customizations\Components\HtmlFeed;
use App\Customizations\Components\JsonFeed;
use App\Customizations\Components\RdfFeed;
use App\Customizations\Components\RssFeed;
use App\Customizations\Composites\DownloadComponent;
use App\Customizations\Composites\ExamineComponent;
use App\Customizations\Facades\FeedFacade;
use App\Customizations\Traits\ErrorCodeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(FeedProxy::class)]
#[UsesClass(FileOpenComponent::class)]
#[UsesClass(ErrorCodeTrait::class)]
#[UsesClass(ExamineComponent::class)]
#[UsesClass(DownloadComponent::class)]
#[UsesClass(FeedFacade::class)]
#[UsesClass(HtmlFeed::class)]
#[UsesClass(JsonFeed::class)]
#[UsesClass(AtomFeed::class)]
#[UsesClass(CsvFeed::class)]
#[UsesClass(RssFeed::class)]
#[UsesClass(RdfFeed::class)]
#[UsesClass(HtmlFeed::class)]
class FeedProxyTest extends TestCase
{
    /**
     * CURL Header Options Property
     *
     * Default values for just examining content and not downloading it
     *
     * @access  public
     * @static
     * @var     array CURL_HEADER
     */
    public const CURL_HEADER = [
        CURLOPT_NOBODY          => true,
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_FILETIME        => true,
    ];

    /**
     * CURL Get Options Property
     *
     * Replaces part of header options to download the source
     *
     * @access  public
     * @static
     * @var     array CURL_GET
     */
    public const CURL_GET = [
        CURLOPT_RETURNTRANSFER  => false,
    ];

    /**
     * Storage Name Property
     *
     * A dummy filesystem storage container for downloaded content.
     *
     * @access  public
     * @static
     * @var     string STORAGE
     */
    public const STORAGE = 'fake_storage';

    /**
     * Download Content
     *
     * Downloads content and returns information related to the content
     *
     * @access  private
     * @static
     * @param   array $acquire Initializer for the composite download method
     * @return  object
     */
    private static function downloader(array $acquire): DownloadComponent
    {
        $acquire += ['disk' => self::STORAGE];

        $examine = new ExamineComponent();
        $examine->acquire((object) $acquire)->execute();

        $download = new DownloadComponent();
        $download->acquire($examine->share());
        $download->execute();
        return $download;
        // return $download->share();
    }

    /**
     * URL Feed Data Provider
     *
     * Contains various links for downloading feed content, for validation and processing
     *
     * @access  public
     * @static
     * @return  string[]
     */
    public static function providerUrls(): array
    {
        return [
            'sample-json'   => [
                self::downloader([
                    'source'    => "https://freetestdata.com/wp-content/uploads/2023/04/1.05KB_JSON-File_FreeTestData.json"
                ]),
                JsonFeed::class,
            ],
            'sample-rdf'    => [
                self::downloader([
                    'source'    => 'https://web.resource.org/rss/1.0/schema.rdf'
                ]),
                RdfFeed::class,
            ],
            'sample-atom'   => [
                self::downloader([
                    'source'    => 'www.intertwingly.net/blog/index.atom'
                ]),
                AtomFeed::class,
            ],
            'sample-csv'    => [
                self::downloader([
                    'source'    => 'https://cdn.wsform.com/wp-content/uploads/2020/06/industry.csv'
                ]),
                CsvFeed::class
            ],
            // 'sample-png'    => [
            //     self::downloader([
            //         'source'    => "https://www.hamiltonstaracademy.com//images/frontpage/portfolio/fullsize/Screenshot1.png"
            //     ]),
            //     null,
            // ],
            'sample-html'   => [
                self::downloader([
                    'source'    => "https://example.com"
                ]),
                HtmlFeed::class,
            ],
        ];
    }

    #[Group('constructor')]
    #[DataProvider('providerUrls')]
    public function test_success_constructor_header(DownloadComponent $component, string $expected): void
    {
        $share = $component->share();
        $extension = \explode('.', $share->filename);
        $extension = \end($extension);

        $facede = new FeedFacade();

        $sut = new FeedProxy($facede->convertor($component));
        $this->assertInstanceOf(FeedProxy::class, $sut);

        $property   = new \ReflectionProperty($sut, 'feed');
        $property->setAccessible(true);
        $feed = $property->getValue($sut);
        $this->assertInstanceOf($expected, $feed);
    }
}
