<?php

declare(strict_types=1);

namespace Tests\Fixtures\Providers;

use App\Customizations\Proxies\interfaces\InterfaceFeed;

final class ExternalProviderJsonFeed
{
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

    /**
     * Data Provider
     *
     * Usable data for SUTs, STUBs and MOCKs
     * Constructor failure data. Some of the fields will be ignored and omitted
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
}