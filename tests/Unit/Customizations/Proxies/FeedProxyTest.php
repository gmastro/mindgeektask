<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Proxies;

use App\Customizations\Components\AtomFeed;
use App\Customizations\Components\CsvFeed;
use App\Customizations\Components\JsonFeed;
use App\Customizations\Components\RdfFeed;
use App\Customizations\Components\RssFeed;
use App\Customizations\Components\XmlFeed;
use App\Customizations\Composites\DownloadComponent;
use App\Customizations\Composites\ExamineComponent;
use App\Customizations\Facades\FeedFacade;
use App\Customizations\Proxies\FeedProxy;
use App\Customizations\Proxies\interfaces\InterfaceFeed;
use Illuminate\Support\Facades\Log;
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
#[UsesClass(JsonFeed::class)]
#[UsesClass(AtomFeed::class)]
#[UsesClass(CsvFeed::class)]
#[UsesClass(RssFeed::class)]
#[UsesClass(RdfFeed::class)]
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
                JsonFeed::class,
            ],
            'sample-rdf'    => [
                [
                    'source'    => 'https://web.resource.org/rss/1.0/schema.rdf'
                ],
                XmlFeed::class,
            ],
            'sample-atom'   => [
                [
                    'source'    => 'www.intertwingly.net/blog/index.atom'
                ],
                AtomFeed::class,
            ],
            'sample-csv'    => [
                [
                    'source'    => 'https://cdn.wsform.com/wp-content/uploads/2020/06/industry.csv'
                ],
                CsvFeed::class
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
            'no-rule'       => [
                [],
                \array_fill_keys($rules, fake()->sentence()),
            ],
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
            'empty'  => [
                [
                    InterfaceFeed::HAS_EXCLUSIVE => [
                        InterfaceFeed::FIELD_CONTENT_TEXT => null,
                        InterfaceFeed::FIELD_CONTENT_HTML => null
                    ]
                ], [

                ]
            ],
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
            "Found none of or mutually exclusive keys: [%s]",
            \implode(",", \array_keys($rules[InterfaceFeed::HAS_EXCLUSIVE]))
        ));
        $sut = new JsonFeed((object) [InterfaceFeed::FIELD_VERSION => "https://jsonfeed.org/version/1.1",]);
        $this->methodSet($sut, 'isExclusive', [$rules, $data]);
    }

    /**
     * Data Provider
     *
     * Usable data for SUTs, STUBs and MOCKs
     * Holds at least one or more selections
     *
     * @access  public
     * @static
     * @return  array<string, array<int, array[]>>
     */
    public static function providerSelectionSuccess(): array
    {
        $rules = fake()->words(11);
        return [
            'selection-01'  => [
                [
                    InterfaceFeed::HAS_SELECTION => [
                        InterfaceFeed::FIELD_CONTENT_TEXT => null,
                        InterfaceFeed::FIELD_CONTENT_HTML => null
                    ],
                ], [
                    InterfaceFeed::FIELD_CONTENT_TEXT => fake()->sentence(),
                ]
            ],
            'selection-02'  => [
                [
                    InterfaceFeed::HAS_SELECTION => [
                        InterfaceFeed::FIELD_CONTENT_TEXT => null,
                        InterfaceFeed::FIELD_CONTENT_HTML => null
                    ],
                ], [
                    InterfaceFeed::FIELD_CONTENT_HTML => \sprintf("<div>%s</div>", fake()->sentence()),
                ]
            ],
            'selection-03'  => [
                [
                    InterfaceFeed::HAS_SELECTION => [
                        InterfaceFeed::FIELD_CONTENT_TEXT => null,
                        InterfaceFeed::FIELD_CONTENT_HTML => null
                    ],
                ], [
                    InterfaceFeed::FIELD_CONTENT_TEXT => fake()->sentence(),
                    InterfaceFeed::FIELD_CONTENT_HTML => \sprintf("<div>%s</div>", fake()->sentence()),
                ]
            ],
            'selection-04'  => [
                [
                    InterfaceFeed::HAS_SELECTION =>  \array_fill_keys($rules, null),
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

    #[Group('success')]
    #[Group('method_isSelection')]
    #[DataProvider('providerSelectionSuccess')]
    public function test_success_is_selection(array $rules, array $data): void
    {
        $sut = new JsonFeed((object) [InterfaceFeed::FIELD_VERSION => "https://jsonfeed.org/version/1.1",]);
        $this->methodSet($sut, 'isSelection', [$rules, $data]);

        $this->assertTrue(true);
    }

    /**
     * Data Provider
     *
     * Usable data for SUTs, STUBs and MOCKs
     * Selection, at least one has to be present. We capture the cases where none is.
     *
     * @access  public
     * @static
     * @return  array<string, array<int, array[]>>
     */
    public static function providerSelectionException(): array
    {
        $rules = fake()->words(11);
        return [
            'empty'  => [
                [
                    InterfaceFeed::HAS_SELECTION => [
                        InterfaceFeed::FIELD_CONTENT_TEXT => null,
                        InterfaceFeed::FIELD_CONTENT_HTML => null
                    ]
                ], [

                ]
            ],
            'nothing-matched'  => [
                [
                    InterfaceFeed::HAS_SELECTION => [
                        InterfaceFeed::FIELD_CONTENT_TEXT => null,
                        InterfaceFeed::FIELD_CONTENT_HTML => null
                    ],
                ], [
                    ...\array_fill_keys($rules, fake()->sentence()),
                ]
            ],
        ];
    }

    #[Group('exception')]
    #[Group('method_isSelection')]
    #[DataProvider('providerSelectionException')]
    public function test_exception_is_selection(array $rules, array $data): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessage(\sprintf(
            "Missing selection of one of available keys: [%s]",
            \implode(",", \array_keys($rules[InterfaceFeed::HAS_SELECTION]))
        ));
        $sut = new JsonFeed((object) [InterfaceFeed::FIELD_VERSION => "https://jsonfeed.org/version/1.1",]);
        $this->methodSet($sut, 'isSelection', [$rules, $data]);
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
}
