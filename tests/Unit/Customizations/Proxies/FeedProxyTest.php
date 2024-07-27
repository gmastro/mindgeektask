<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Proxies;

use App\Customizations\Components\Feeds\AtomFeedComponent;
use App\Customizations\Components\Feeds\CsvFeedComponent;
use App\Customizations\Components\Feeds\JsonFeedComponent;
use App\Customizations\Components\Feeds\RdfFeedComponent;
use App\Customizations\Components\Feeds\RssFeedComponent;
use App\Customizations\Components\Feeds\XmlFeedComponent;
use App\Customizations\Composites\DownloadComponent;
use App\Customizations\Composites\ExamineComponent;
use App\Customizations\Facades\FeedFacade;
use App\Customizations\Proxies\FeedProxy;
use Tests\Fixtures\Traits\ReflectionTrait;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(FeedProxy::class)]
#[UsesClass(ExamineComponent::class)]
#[UsesClass(DownloadComponent::class)]
#[UsesClass(FeedFacade::class)]
#[UsesClass(JsonFeedComponent::class)]
#[UsesClass(AtomFeedComponent::class)]
#[UsesClass(CsvFeedComponent::class)]
#[UsesClass(RssFeedComponent::class)]
#[UsesClass(RdfFeedComponent::class)]
#[UsesClass(XmlFeedComponent::class)]
class FeedProxyTest extends TestCase
{
    use ReflectionTrait;

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

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake(self::STORAGE);
    }

    /**
     * Download Content
     *
     * Downloads content and returns information related to the content
     *
     * @access  private
     * @param   array $acquire Initializer for the composite download method
     * @return  DownloadComponent
     */
    private function downloader(array $acquire): DownloadComponent
    {
        $acquire += ['disk' => self::STORAGE];

        $examine = new ExamineComponent();
        $examine->acquire((object) $acquire)->execute();

        $download = new DownloadComponent();
        $download->acquire($examine->share());
        $download->execute();
        return $download;
    }

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

    #[Group('constructor')]
    #[Group('success')]
    #[DataProvider('providerSuccessUrls')]
    public function test_success_constructor_header(array $acquire, string $expected): void
    {
        $facade = new FeedFacade();
        $sut = new FeedProxy($facade->convertor($this->downloader($acquire)));
        $this->assertInstanceOf(FeedProxy::class, $sut);

        $feed = $this->propertyGet($sut, 'feed');
        $this->assertInstanceOf($expected, $feed);
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

    #[Group('constructor')]
    #[Group('exception')]
    #[DataProvider('providerExceptionUrls')]
    public function test_exception_constructor_header(array $acquire): void
    {
        $this->expectException(\TypeError::class);
        $facade = new FeedFacade();
        $sut = new FeedProxy($facade->convertor($this->downloader($acquire)));
    }
}
