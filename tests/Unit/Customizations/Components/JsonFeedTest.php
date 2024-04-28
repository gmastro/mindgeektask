<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Components;

use App\Customizations\Components\JsonFeed;
use App\Customizations\Proxies\interfaces\InterfaceFeed;
use Tests\Fixtures\Traits\ReflectionTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(JsonFeed::class)]
#[UsesClass(InterfaceFeed::class)]
class JsonFeedTest extends TestCase
{
    use ReflectionTrait;

    public static function providerSuccessJson(): array
    {
        return [
            'version-1-0'   => [(object) [
                InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1",
                InterfaceFeed::FIELD_TITLE          => fake()->sentence(),
                InterfaceFeed::FIELD_HOME_PAGE_URL  => fake()->url(),
                InterfaceFeed::FIELD_FEED_URL       => \implode("/", [fake()->url(), "feed.json"]),
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
            'version-1-1'   => [(object) [
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
        ];
    }

    #[Group('success')]
    #[Group('construct')]
    #[DataProvider('providerSuccessJson')]
    public function test_success_construct(object $feed): void
    {
        $sut = new JsonFeed($feed);

        $isValid = $this->propertyGet($sut, 'isValid');
        $json = $this->propertyGet($sut, 'json');

        $this->assertTrue($isValid);
        $this->assertNotEmpty($json);
    }

    /**
     * Invalid Json Urls
     *
     * Contains various links for downloading feed content, for validation and processing
     *
     * @access  public
     * @static
     * @return  array<string, array<string, string>|string>
     */
    public static function providerFailureJson(): array
    {
        return [
            'version-1-0'   => [(object) []],
            'version-1-1'   => [(object) []],
        ];
    }

    #[Group('failure')]
    #[Group('construct')]
    #[DataProvider('providerFailureJson')]
    public function test_failure_construct(object $feed): void
    {
        $sut = new JsonFeed($feed);

        $isValid = $this->propertyGet($sut, 'isValid');
        $this->assertFalse($isValid);
    }

    #[Group('current')]
    #[Group('success')]
    #[Group('method_execute')]
    #[DataProvider('providerSuccessJson')]
    public function test_success_execute(object $feed): void
    {
        $sut = new JsonFeed($feed);

        $this->assertTrue($sut->execute());
    }
}
