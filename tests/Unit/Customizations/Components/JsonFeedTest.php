<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Components;

use App\Customizations\Components\JsonFeed;
use App\Customizations\Proxies\interfaces\InterfaceFeed;
use Faker\Provider\ar_EG\Internet;
use Illuminate\Support\Facades\Log;
use PhpParser\Builder\Interface_;
use Tests\Fixtures\Traits\ReflectionTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;
use ValueError;

#[CoversClass(JsonFeed::class)]
#[UsesClass(InterfaceFeed::class)]
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

        $isValid = $this->propertyGet($sut, 'isValid');
        $json = $this->propertyGet($sut, 'json');

        $this->assertTrue($isValid);
        $this->assertNotEmpty($json);
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

    #[Group('failure')]
    #[Group('construct')]
    #[DataProvider('providerEmptyJson')]
    public function test_failure_construct(object $feed): void
    {
        $sut = new JsonFeed($feed);

        $isValid = $this->propertyGet($sut, 'isValid');
        $this->assertFalse($isValid);
    }

    /**
     * Data Provider
     *
     * Usable data for SUTs, STUBs and MOCKs
     * Exclusions, as you may have only a single entry available from all those provided
     *
     * @access  public
     * @static
     * @return  array<string, array<int, array[]>>
     */
    public static function providerExclusiveSuccess(): array
    {
        $random = \rand(2,10);
        $rules = fake()->words($random);
        return [
            'exclusive-01'  => [
                [
                    InterfaceFeed::HAS_EXCLUSIVE => [
                        InterfaceFeed::FIELD_CONTENT_TEXT => null,
                        InterfaceFeed::FIELD_CONTENT_HTML => null
                    ]
                ], [
                    InterfaceFeed::FIELD_CONTENT_TEXT => fake()->sentence(),
                    ...\array_fill_keys($rules, fake()->sentence()),
                ]
            ],
            'exclusive-02'  => [
                [
                    InterfaceFeed::HAS_EXCLUSIVE => [
                        InterfaceFeed::FIELD_CONTENT_TEXT => null,
                        InterfaceFeed::FIELD_CONTENT_HTML => null
                    ],
                ], [
                    InterfaceFeed::FIELD_CONTENT_HTML => \sprintf("<div>%s</div>", fake()->sentence()),
                    ...\array_fill_keys($rules, fake()->sentence()),
                ]
            ],
            'exclusive-03'  => [
                [
                    InterfaceFeed::HAS_EXCLUSIVE => \array_fill_keys($rules, null),
                ], [
                    $rules[$random - 1] => fake()->boolean(),
                ]
            ],
        ];
    }

    #[Group('success')]
    #[Group('method_isExclusive')]
    #[DataProvider('providerExclusiveSuccess')]
    public function test_success_is_exclusive(array $rules, array $data): void
    {
        $sut = new JsonFeed((object) [InterfaceFeed::FIELD_VERSION => "https://jsonfeed.org/version/1.1",]);
        $this->methodSet($sut, 'isExclusive', [$rules, $data]);

        $this->assertTrue(true);
    }

    /**
     * Data Provider
     *
     * Usable data for SUTs, STUBs and MOCKs
     * Exclusions, this will confirm that 2 or more fields cannot appear the very same time
     *
     * @access  public
     * @static
     * @return  array<string, array<int, array[]>>
     */
    public static function providerExclusiveException(): array
    {
        $rules = fake()->words(11);
        return [
            'exclusive-01'  => [
                [
                    InterfaceFeed::HAS_EXCLUSIVE => [
                        InterfaceFeed::FIELD_CONTENT_TEXT => null,
                        InterfaceFeed::FIELD_CONTENT_HTML => null
                    ],
                ], [
                    InterfaceFeed::FIELD_CONTENT_TEXT => fake()->sentence(),
                    InterfaceFeed::FIELD_CONTENT_HTML => \sprintf("<div>%s</div>", fake()->sentence()),
                    ...\array_fill_keys($rules, fake()->sentence()),
                ]
            ],
            'exclusive-02'  => [
                [
                    InterfaceFeed::HAS_EXCLUSIVE =>  \array_fill_keys($rules, null),
                ], [
                    $rules[0] => fake()->boolean(),
                    $rules[1] => fake()->numerify("## what ##"),
                    $rules[2] => fake()->uuid(),
                    $rules[3] => fake()->lexify("?? what ??"),
                    $rules[5] => fake()->email(),
                    $rules[7] => fake()->date(),
                ]
            ],
        ];
    }

    #[Group('exception')]
    #[Group('method_isExclusive')]
    #[DataProvider('providerExclusiveException')]
    public function test_exception_is_exclusive(array $rules, array $data): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage(\sprintf(
            "Found mutually exclusive keys: [%s]",
            \implode(",", \array_keys($rules[InterfaceFeed::HAS_EXCLUSIVE]))
        ));
        $sut = new JsonFeed((object) [InterfaceFeed::FIELD_VERSION => "https://jsonfeed.org/version/1.1",]);
        $this->methodSet($sut, 'isExclusive', [$rules, $data]);
    }

    #[Group('current')]
    #[Group('success')]
    #[Group('method_isSelection')]
    #[DataProvider('providerExclusiveSuccess')]
    public function test_success_is_selection(array $rules, array $data): void
    {
        $this->markTestIncomplete('need to implement logic');
    }

    #[Group('exception')]
    #[Group('method_isSelection')]
    public function test_exception_is_selection(): void
    {
        $this->markTestIncomplete('need to implement logic');
    }

    #[Group('success')]
    #[Group('method_validate')]
    public function test_success_validate(): void
    {
        $sut = new JsonFeed((object) [
            InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1.1",
        ]);

        $result = $this->methodSet($sut, 'validate', [[
            [
                InterfaceFeed::FIELD_ID     => 1,
                InterfaceFeed::FIELD_TITLE  => "title",
            ]
        ], [
            InterfaceFeed::IS_ARRAY => InterfaceFeed::IS_OBJECT,
        ]]);

        $this->assertTrue($result);
    }

    /**
     * Data Provider
     *
     * Usable for the given SUT, or STUB, or MOCK.
     * Holds data type variations that will not match our condition
     *
     * @access  public
     * @return  array
     */
    public static function providerValidationFailure(): array
    {
        return [
            'is-array' => [
                null,
                [
                    InterfaceFeed::IS_NUMERIC   => null,
                    InterfaceFeed::IS_STRING    => null,
                    InterfaceFeed::IS_BOOL      => null,
                    InterfaceFeed::IS_OBJECT    => null,
                ]
            ],
            'is_object' => [
                (object) [null],
                [
                    InterfaceFeed::IS_ARRAY     => null,
                ]
            ]
        ];
    }

    #[Group('failure')]
    #[Group('method_validate')]
    #[DataProvider('providerValidationFailure')]
    public function test_failure_validate(): void
    {
        $sut = new JsonFeed((object) [InterfaceFeed::FIELD_VERSION => "https://jsonfeed.org/version/1.1",]);

        $result = $this->methodSet($sut, 'validate', \func_get_args());

        $mock = Log::partialMock();
        $mock->shouldNotHaveReceived('info');

        $this->assertFalse($result);
    }

    /**
     * Data Provider
     *
     * Usable for the given SUT, or STUB, or MOCK.
     * Holds data type variations that will not match our condition
     *
     * @access  public
     * @return  array
     */
    public static function providerValidationPredictFailure(): array
    {
        return [
            'is-array' => [
                'This will create 2 info messages, one for associative array and one for bool[]',
                [
                    InterfaceFeed::IS_ARRAY     => InterfaceFeed::IS_OBJECT,
                    InterfaceFeed::IS_ARRAY     => InterfaceFeed::IS_BOOL,
                ]
            ],
        ];
    }

    #[Group('failure')]
    #[Group('method_validate')]
    #[DataProvider('providerValidationPredictFailure')]
    public function test_failure_validate_catch(): void
    {
        $sut = new JsonFeed((object) [InterfaceFeed::FIELD_VERSION => "https://jsonfeed.org/version/1.1",]);

        $result = $this->methodSet($sut, 'validate', \func_get_args());

        $mock = Log::partialMock();
        $method = \sprintf("%s::validate", \get_class($sut));
        $got = \gettype(\func_get_arg(0));

        foreach(\func_get_arg(1) as $k => $v) {
            $expected = $v === null ? $k : \sprintf("%s -> %s", $k, $v);

            $mock->shouldReceive('info')
                ->with(\sprintf("%s. Expected: [%s], Got: %s", $method, $expected, $got), [
                    'method' => $method,
                    'expected' => $expected, 'got' => $got]
                );
        }

        $this->assertFalse($result);
    }

    #[Group('success')]
    #[Group('method_capture')]
    #[DataProvider('providerSuccessJson')]
    public function test_success_capture(object $feed): void
    {
        $sut = new JsonFeed($feed);

        $json = $this->propertyGet($sut, 'json');
        $result = $this->methodSet($sut, 'capture', [$sut::VERSIONS[$sut::VERSION_X_X], $json]);
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    /**
     * Data Provider
     *
     * Usable data for SUTs, STUBs and MOCKs
     * Constructor exception data
     *
     * @access  public
     * @static
     * @return  array<string, array<int, object>>
     */
    public static function providerFailureJson(): array
    {
        return [
            'version-1.0'   => [
                (object) [
                    InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1",
                    InterfaceFeed::FIELD_TITLE          => fake()->sentence(),
                    InterfaceFeed::FIELD_HOME_PAGE_URL  => false,
                    InterfaceFeed::FIELD_FEED_URL       => NAN,
                    InterfaceFeed::FIELD_ITEMS          => [
                        [
                            InterfaceFeed::FIELD_ID             => 123,
                            InterfaceFeed::FIELD_TITLE          => fake()->sentence(),
                            InterfaceFeed::FIELD_AUTHOR         => [],
                            InterfaceFeed::FIELD_CONTENT_TEXT   => fake()->paragraph(),
                        ]
                    ],
                ],
            ],
            'version-1.1'   => [
                (object) [
                    InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1.1",
                    InterfaceFeed::FIELD_TITLE          => fake()->sentence(),
                    InterfaceFeed::FIELD_HOME_PAGE_URL  => false,
                    InterfaceFeed::FIELD_FEED_URL       => NAN,
                    InterfaceFeed::FIELD_ITEMS          => [
                        [
                            InterfaceFeed::FIELD_ID             => 123,
                            InterfaceFeed::FIELD_TITLE          => fake()->sentence(),
                            InterfaceFeed::FIELD_CONTENT_TEXT   => fake()->paragraph(),
                        ],
                    ]
                ],
            ],
        ];
    }

    #[Group('failure')]
    #[Group('method_capture')]
    #[DataProvider('providerFailureJson')]
    public function test_failure_capture(object $feed): void
    {
        $sut = new JsonFeed($feed);

        $json = $this->propertyGet($sut, 'json');
        $result = $this->methodSet($sut, 'capture', [$sut::VERSIONS[$sut::VERSION_X_X], $json]);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey(InterfaceFeed::FIELD_TITLE, $result);
        $this->assertArrayHasKey(InterfaceFeed::FIELD_ITEMS, $result);
        $this->assertArrayNotHasKey(InterfaceFeed::FIELD_HOME_PAGE_URL, $result);
        $this->assertArrayNotHasKey(InterfaceFeed::FIELD_FEED_URL, $result);
        $this->assertArrayNotHasKey(InterfaceFeed::FIELD_AUTHOR, $result[InterfaceFeed::FIELD_ITEMS][0]);
    }

    /**
     * Data Provider
     *
     * Usable data for SUTs, STUBs and MOCKs
     * Constructor exception data
     *
     * @access  public
     * @static
     * @return  array<string, array<int, object>>
     */
    public static function providerExceptionJson(): array
    {
        return [
            'empty-version-1'                   => [(object) [
                InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1",
            ]],
            'invalid-data-type-on-title'     => [(object) [
                InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1",
                InterfaceFeed::FIELD_TITLE          => 42,
                InterfaceFeed::FIELD_HOME_PAGE_URL  => false,
                InterfaceFeed::FIELD_FEED_URL       => NAN,
                InterfaceFeed::FIELD_ITEMS          => [
                    [
                        InterfaceFeed::FIELD_ID             => true,
                        InterfaceFeed::FIELD_TITLE          => [],
                        InterfaceFeed::FIELD_AUTHORS        => [],
                    ], [

                    ],
                ]
            ]],
            'malformed-children-version-1'      => [(object) [
                InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1",
                InterfaceFeed::FIELD_TITLE          => fake()->sentence(),
                InterfaceFeed::FIELD_HOME_PAGE_URL  => fake()->url(),
                InterfaceFeed::FIELD_FEED_URL       => \implode("/", [fake()->url(), "feed.json"]),
                InterfaceFeed::FIELD_ITEMS          => 'should be an array here'
            ]],
            'malformed-children-version-2'     => [(object) [
                InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1",
                InterfaceFeed::FIELD_TITLE          => fake()->sentence(),
                InterfaceFeed::FIELD_HOME_PAGE_URL  => false,
                InterfaceFeed::FIELD_FEED_URL       => NAN,
                InterfaceFeed::FIELD_ITEMS          => [
                    [
                        InterfaceFeed::FIELD_ID             => true,
                        InterfaceFeed::FIELD_TITLE          => [],
                        InterfaceFeed::FIELD_AUTHORS        => [],
                    ],
                ]
            ]],
            'malformed-children-version-3'     => [(object) [
                InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1",
                InterfaceFeed::FIELD_TITLE          => fake()->sentence(),
                InterfaceFeed::FIELD_HOME_PAGE_URL  => false,
                InterfaceFeed::FIELD_FEED_URL       => NAN,
                InterfaceFeed::FIELD_ITEMS          => [
                    [
                        InterfaceFeed::FIELD_ID             => true,
                        InterfaceFeed::FIELD_TITLE          => [],
                        InterfaceFeed::FIELD_AUTHORS        => [],
                    ], [

                    ],
                ]
            ]],
            'empty-children-version-1'      => [(object) [
                InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1",
                InterfaceFeed::FIELD_TITLE          => fake()->sentence(),
                InterfaceFeed::FIELD_HOME_PAGE_URL  => fake()->url(),
                InterfaceFeed::FIELD_FEED_URL       => \implode("/", [fake()->url(), "feed.json"]),
                InterfaceFeed::FIELD_ITEMS          => [],
            ]],
            'empty-version-1.0'                 => [(object) [
                InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1.0",
            ]],
            'empty-version-1.1'                 => [(object) [
                InterfaceFeed::FIELD_VERSION        => "https://jsonfeed.org/version/1.1",
            ]],
        ];
    }

    #[Group('exception')]
    #[Group('method_capture')]
    #[DataProvider('providerExceptionJson')]
    public function test_exception_capture(object $feed): void
    {
        $this->expectException(\ValueError::class);
        $sut = new JsonFeed($feed);

        $json = $this->propertyGet($sut, 'json');
        $this->methodSet($sut, 'capture', [$sut::VERSIONS[$sut::VERSION_X_X], $json]);
    }

    #[Group('success')]
    #[Group('method_execute')]
    #[DataProvider('providerSuccessJson')]
    public function test_success_execute(object $feed): void
    {
        $sut = new JsonFeed($feed);

        $this->assertTrue($sut->execute());
    }
}
