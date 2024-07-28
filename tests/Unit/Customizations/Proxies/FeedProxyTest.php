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
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Fixtures\Providers\ExternalProviderFeedUrls;
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
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();
        Storage::fake(TestCase::STORAGE);
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
        $acquire += ['disk' => TestCase::STORAGE];

        $examine = new ExamineComponent();
        $examine->acquire((object) $acquire)->execute();

        $download = new DownloadComponent();
        $download->acquire($examine->share());
        $download->execute();
        return $download;
    }

    #[Group('constructor')]
    #[Group('success')]
    #[DataProviderExternal(ExternalProviderFeedUrls::class, 'providerSuccessUrls')]
    public function test_success_constructor_header(array $acquire, string $expected): void
    {
        $facade = new FeedFacade();
        $sut = new FeedProxy($facade->convertor($this->downloader($acquire)));
        $this->assertInstanceOf(FeedProxy::class, $sut);

        $feed = $this->propertyGet($sut, 'feed');
        $this->assertInstanceOf($expected, $feed);
    }

    #[Group('constructor')]
    #[Group('exception')]
    #[DataProviderExternal(ExternalProviderFeedUrls::class, 'providerExceptionUrls')]
    public function test_exception_constructor_header(array $acquire): void
    {
        $this->expectException(\TypeError::class);
        $facade = new FeedFacade();
        $sut = new FeedProxy($facade->convertor($this->downloader($acquire)));
    }
}
