<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Components;

use App\Customizations\Components\JsonFeed;
use App\Customizations\Facades\FeedFacade;
use App\Customizations\Proxies\interfaces\InterfaceFeed;
use Illuminate\Support\Facades\Log;
use Tests\Fixtures\Traits\ReflectionTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\Fixtures\Providers\ExternalProviderJsonFeed;
use Tests\TestCase;
use ValueError;

#[CoversClass(JsonFeed::class)]
#[UsesClass(InterfaceFeed::class)]
#[UsesClass(FeedFacade::class)]
class JsonFeedTest extends TestCase
{
    use ReflectionTrait;

    #[Group('success')]
    #[Group('construct')]
    #[DataProviderExternal(ExternalProviderJsonFeed::class, 'providerSuccessJson')]
    public function test_success_construct(object $feed): void
    {
        $sut = new JsonFeed($feed);

        $json = $this->propertyGet($sut, 'json');
        $version = $this->propertyGet($sut, 'version');
        $callback = $this->propertyGet($sut, 'callback');

        $this->assertFalse($sut->hasErrors());
        $this->assertNotEmpty($json);
        $this->assertNotSame("", $version);
        $this->assertIsCallable($callback);
    }

    /**
     * Data Provider
     *
     * Usable data for SUTs, STUBs and MOCKs
     * Invalid Json content for the different set of available versions for downloading feed content, for validation
     * and processing.
     *
     * @access  public
     * @static
     * @return  array<string, array<int, object>>
     */
    public static function providerEmptyJson(): array
    {
        return [
            'version-1.0'   => [(object) []],
            'version-1.1'   => [(object) []],
        ];
    }

    /**
     * Data Provider
     *
     * Usable data for SUTs, STUBs and MOCKs
     * Invalid or unsupported json versions.
     *
     * @access  public
     * @static
     * @return  array<string, array<int, object>>
     */
    public static function providerUnsupportedInvalidVersions(): array
    {
        return [
            'invalid'       => [(object)[InterfaceFeed::FIELD_VERSION => 'not a matching version string']],
            'unsupported'   => [(object)[InterfaceFeed::FIELD_VERSION => 'https://jsonfeed.org/version/100.1']],
        ];
    }

    #[Group('failure')]
    #[Group('construct')]
    #[DataProvider('providerEmptyJson')]
    #[DataProvider('providerUnsupportedInvalidVersions')]
    public function test_failure_construct(object $feed): void
    {
        $sut = new JsonFeed($feed);

        $version = $this->propertyGet($sut, 'version');

        $this->assertTrue($sut->hasErrors());
        $this->assertSame("", $version);
    }

    #[Group('success')]
    #[Group('method_sanitize')]
    #[Group('method_get_context')]
    #[DataProviderExternal(ExternalProviderJsonFeed::class, 'providerSuccessJson')]
    public function test_success_sanitize(object $feed): void
    {
        $sut = new JsonFeed($feed);

        $this->assertTrue($sut->sanitize());
        $this->assertNotEmpty($sut->getContext());

        $mock = Log::partialMock();
        $mock->shouldNotHaveReceived('error');
    }

    #[Group('failure')]
    #[Group('method_sanitize')]
    #[Group('method_get_context')]
    #[DataProvider('providerEmptyJson')]
    #[DataProvider('providerUnsupportedInvalidVersions')]
    public function test_failure_sanitize(object $feed): void
    {
        $sut = new JsonFeed($feed);
        $this->assertFalse($sut->sanitize());
        $this->assertEmpty($sut->getContext());

        $mock = Log::partialMock();
        $mock->shouldNotHaveReceived('error');
    }

    #[Group('failure')]
    #[Group('method_sanitize')]
    #[Group('method_get_context')]
    #[DataProviderExternal(ExternalProviderJsonFeed::class, 'providerFailureJson')]
    public function test_failure_sanitize_validation(object $feed): void
    {
        $sut = new JsonFeed($feed);
        $this->assertTrue($sut->sanitize());

        $context = $sut->getContext();
        $this->assertNotEmpty($context);

        $this->assertArrayHasKey(InterfaceFeed::FIELD_TITLE, $context);
        $this->assertArrayHasKey(InterfaceFeed::FIELD_ITEMS, $context);
        $this->assertArrayNotHasKey(InterfaceFeed::FIELD_HOME_PAGE_URL, $context);
        $this->assertArrayNotHasKey(InterfaceFeed::FIELD_FEED_URL, $context);
        $this->assertArrayNotHasKey(InterfaceFeed::FIELD_AUTHOR, $context[InterfaceFeed::FIELD_ITEMS][0]);
        
        $mock = Log::partialMock();
        $mock->shouldNotHaveReceived('error');
    }

    #[Group('failure')]
    #[Group('method_sanitize')]
    #[Group('method_get_context')]
    #[DataProviderExternal(ExternalProviderJsonFeed::class, 'providerExceptionJson')]
    public function test_failure_sanitize_exception(object $feed): void
    {
        $sut = new JsonFeed($feed);

        $mock = Log::partialMock();
        $mock->shouldReceive('error');

        $this->assertFalse($sut->sanitize());
        $this->assertTrue($sut->hasErrors());
    }

    #[Group('success')]
    #[Group('method_execute')]
    #[Group('method_get_context')]
    #[DataProviderExternal(ExternalProviderJsonFeed::class, 'providerSuccessJson')]
    public function test_success_execute(object $feed): void
    {
        $sut = new JsonFeed($feed);

        $this->assertTrue($sut->execute());
        $this->assertNotEmpty($sut->getContext());
    }

    #[Group('failure')]
    #[Group('method_execute')]
    #[Group('method_get_context')]
    #[DataProvider('providerEmptyJson')]
    public function test_failure_execute(object $feed): void
    {
        $sut = new JsonFeed($feed);
        $this->assertFalse($sut->execute());
        $this->assertEmpty($sut->getContext());
    }
}
