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

    /**
     * Data Provider
     *
     * Usable data for SUTs, STUBs and MOCKs
     * Current one provides some success sample data for valid json feeds.
     *
     * @access  public
     * @static
     * @return  array<string, array<int, object>>
     */
    public static function providerSuccessJson(): array
    {
        return [
            'version-1.0'   => [(object) [
                InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1",
                InterfaceFeed::FIELD_TITLE          => fake()->sentence(),
                InterfaceFeed::FIELD_HOME_PAGE_URL  => fake()->url(),
                InterfaceFeed::FIELD_FEED_URL       => \implode("/", [fake()->url(), "feed.json"]),
                InterfaceFeed::FIELD_AUTHOR         => [
                    InterfaceFeed::FIELD_NAME           => \implode(" ", [fake()->firstName(), fake()->lastName()]),
                ],
                InterfaceFeed::FIELD_HUBS           => [
                    [
                        InterfaceFeed::FIELD_URL            => fake()->url(),
                        InterfaceFeed::FIELD_TYPE           => [
                            InterfaceFeed::FIELD_WEBSUB         => [
                                InterfaceFeed::FIELD_MODE           => "subscribe",
                                InterfaceFeed::FIELD_REASON         => "That's the way, I like it!",
                            ]
                        ]
                    ]
                ],
                InterfaceFeed::FIELD_ITEMS          => [
                    [
                        InterfaceFeed::FIELD_ID             => fake()->randomDigit(),
                        InterfaceFeed::FIELD_CONTENT_TEXT   => fake()->text(),
                        InterfaceFeed::FIELD_URL            => \implode("/", [fake()->url(), fake()->randomDigit()]),
                    ], [
                        InterfaceFeed::FIELD_ID             => fake()->randomDigit(),
                        InterfaceFeed::FIELD_CONTENT_HTML   => fake()->text(),
                        InterfaceFeed::FIELD_URL            => \implode("/", [fake()->url(), fake()->randomDigit()]),
                    ],
                ],
            ]],
            'version-1.1'   => [(object) [
                InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1.1",
                InterfaceFeed::FIELD_TITLE          => fake()->sentence(),
                InterfaceFeed::FIELD_HOME_PAGE_URL  => fake()->url(),
                InterfaceFeed::FIELD_FEED_URL       => fake()->url() . "/feed.json",
                InterfaceFeed::FIELD_ITEMS          => [
                    [
                        InterfaceFeed::FIELD_ID             => fake()->randomDigit(),
                        InterfaceFeed::FIELD_CONTENT_TEXT   => fake()->text(),
                        InterfaceFeed::FIELD_URL            => \implode("/", [fake()->url(), fake()->randomDigit()]),
                    ], [
                        InterfaceFeed::FIELD_ID             => fake()->randomDigit(),
                        InterfaceFeed::FIELD_CONTENT_HTML   => fake()->text(),
                        InterfaceFeed::FIELD_URL            => \implode("/", [fake()->url(), fake()->randomDigit()]),
                    ],
                ],
            ]],
            'version-1.1-extended' => [(object) [
                InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1.1",
                InterfaceFeed::FIELD_TITLE          => fake()->sentence(),
                InterfaceFeed::FIELD_HOME_PAGE_URL  => fake()->url(),
                InterfaceFeed::FIELD_FEED_URL       => fake()->url() . "/feed.json",
                InterfaceFeed::FIELD_AUTHORS        => [
                    [
                        InterfaceFeed::FIELD_NAME       => \sprintf("%s %s", fake()->firstName(), fake()->lastName()),
                        InterfaceFeed::FIELD_URL        => fake()->url(),
                        InterfaceFeed::FIELD_AVATAR     => fake()->url(),
                    ]
                ],
                InterfaceFeed::FIELD_ITEMS          => [
                    [
                        InterfaceFeed::FIELD_ID             => fake()->randomDigit(),
                        InterfaceFeed::FIELD_CONTENT_TEXT   => fake()->text(),
                        InterfaceFeed::FIELD_CONTENT_HTML   => \sprintf("<div>%s</div>", fake()->text()),
                        InterfaceFeed::FIELD_URL            => \implode("/", [fake()->url(), fake()->randomDigit()]),
                        InterfaceFeed::FIELD_SUMMARY        => fake()->sentence(),
                        InterfaceFeed::FIELD_DATE_PUBLISHED => now()->subDays(1)->toDateTimeString(),
                        InterfaceFeed::FIELD_ATTACHMENTS    => [
                            [
                                InterfaceFeed::FIELD_URL                => fake()->url(),
                                InterfaceFeed::FIELD_MIME_TYPE          => fake()->mimeType(),
                                InterfaceFeed::FIELD_SIZE_IN_BYTES      => fake()->numberBetween(),
                                InterfaceFeed::FIELD_DURATION_IN_SECONDS=> fake()->numberBetween(60, 3600),
                            ], [
                                InterfaceFeed::FIELD_URL                => fake()->url(),
                                InterfaceFeed::FIELD_MIME_TYPE          => fake()->mimeType(),
                                InterfaceFeed::FIELD_SIZE_IN_BYTES      => fake()->numberBetween(),
                                InterfaceFeed::FIELD_DURATION_IN_SECONDS=> fake()->numberBetween(60, 3600),
                            ]
                        ]
                    ], [
                        InterfaceFeed::FIELD_ID             => fake()->randomDigit(),
                        InterfaceFeed::FIELD_CONTENT_TEXT   => fake()->text(),
                        InterfaceFeed::FIELD_CONTENT_HTML   => \sprintf("<div>%s</div>", fake()->text()),
                        InterfaceFeed::FIELD_URL            => \implode("/", [fake()->url(), fake()->randomDigit()]),
                    ],
                ],
            ]],
        ];
    }

    #[Group('success')]
    #[Group('construct')]
    #[DataProvider('providerSuccessJson')]
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
    #[DataProvider('providerSuccessJson')]
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
    #[DataProvider('providerSuccessJson')]
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
