<?php

/**
 * Class JsonFeed | ./app/Customizations/Components/JsonFeed.php
 *
 * Prepares the content for storage or in case of exclusive defined structure returns the content and process it as is.
 *
 * @package     Feeds
 * @subpackage  Customizations
 * @author      George Mastrovasilis <george.mastrovasilis@gmail.com>
 * @copyright   Copyright (c) 2023, George Mastrovasilis
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link        https://github.com/gmastro/mindgeektask/tree/master/app/Customizations/Components/JsonFeed.php
 */

declare(strict_types=1);

namespace App\Customizations\Components;

use App\Customizations\Components\interfaces\InterfaceErrorCodes;
use App\Customizations\Facades\FeedFacade;
use App\Customizations\Proxies\interfaces\InterfaceFeed;
use App\Customizations\Traits\ErrorCodeTrait;
use Illuminate\Support\Facades\Log;

/**
 * JSON Feed
 *
 * From downloaded reads and restructures the content based on {@link https://www.jsonfeed.org/version/x.x/}
 *
 * @category    Components
 * @package     Feeds
 * @version     0.0.1
 */
class JsonFeed implements InterfaceFeed
{
    use ErrorCodeTrait;

    /**
     * Versions
     *
     * Regular expression to match versions
     *
     * @access  public
     * @static
     * @var     string VERSION_X_X
     */
    public const VERSION_X_X = "%^https://(www\.)?jsonfeed.org/version/(\d+(\.\d+)?)$%";

    /**
     * Versions
     *
     * The link for the given version
     *
     * @access  public
     * @static
     * @var     string VERSION_1_0
     */
    public const VERSION_1_0 = "https://www.jsonfeed.org/version/1";

    /**
     * Versions
     *
     * The link for the given version
     *
     * @access  public
     * @static
     * @var     string VERSION_1_1
     */
    public const VERSION_1_1 = "https://www.jsonfeed.org/version/1.1";

    /**
     * Versions
     *
     * Holds active versions and those properties which are required or optional
     * Some of the properties are extended for specific structure validations.
     *
     * @access  public
     * @static
     * @var     array VERSIONS
     */
    public const VERSIONS = [
        self::VERSION_X_X               => [
            self::FIELD_VERSION             => [
                self::IS_REQUIRED               => true,
                self::DATATYPES                 => [
                    self::IS_STRING                 => null,
                ],
            ],
            self::FIELD_TITLE               => [
                self::IS_REQUIRED               => true,
                self::DATATYPES                 => [
                    self::IS_STRING                 => null,
                ],
            ],
            self::FIELD_ICON                => [
                self::IS_OPTIONAL               => true,
                self::DATATYPES                 => [
                    self::IS_STRING                 => null,
                ],
            ],
            self::FIELD_HOME_PAGE_URL       => [
                self::IS_OPTIONAL               => true,
                self::DATATYPES                 => [
                    self::IS_STRING                 => null,
                ],
            ],
            self::FIELD_FEED_URL            => [
                self::IS_OPTIONAL               => true,
                self::DATATYPES                 => [
                    self::IS_STRING                 => null,
                ],
            ],
            self::FIELD_DESCRIPTION         => [
                self::IS_OPTIONAL               => true,
                self::DATATYPES                 => [
                    self::IS_STRING                 => null,
                ],
            ],
            self::FIELD_USER_COMMENT        => [
                self::IS_OPTIONAL               => true,
                self::DATATYPES                 => [
                    self::IS_STRING                 => null,
                ],
            ],
            self::FIELD_NEXT_URL            => [
                self::IS_OPTIONAL               => true,
                self::DATATYPES                 => [
                    self::IS_STRING                 => null,
                ],
            ],
            self::FIELD_ICON                => [
                self::IS_OPTIONAL               => true,
                self::DATATYPES                 => [
                    self::IS_STRING                 => null,
                ],
            ],
            self::FIELD_FAVICON             => [
                self::IS_OPTIONAL               => true,
                self::DATATYPES                 => [
                    self::IS_STRING                 => null,
                ],
            ],
            self::FIELD_LANGUAGE            => [
                self::IS_OPTIONAL               => true,
                self::DATATYPES                 => [
                    self::IS_STRING                 => null,
                ],
            ],
            self::FIELD_EXPIRED             => [
                self::IS_OPTIONAL               => true,
                self::DATATYPES                 => [
                    self::IS_BOOL                   => null,
                ],
            ],
            self::FIELD_HUBS                => [
                self::IS_OPTIONAL               => true,
                self::DATATYPES                 => [
                    self::IS_ARRAY                  => self::IS_OBJECT,
                ],
                self::CHILDREN                  => [
                    self::IS_REQUIRED               => true,
                    self::FIELD_URL                 => [
                        self::IS_REQUIRED               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_TYPE                => [
                        self::IS_REQUIRED               => true,
                        self::DATATYPES                 => [
                            self::IS_OBJECT                 => null,
                        ],
                        self::CHILDREN                  => [
                            self::IS_REQUIRED               => true,
                            self::FIELD_WEBSUB              => [
                                self::IS_REQUIRED               => true,
                                self::DATATYPES                 => [
                                    self::IS_OBJECT                 => null,
                                ],
                                self::CHILDREN                  => [
                                    self::IS_REQUIRED               => true,
                                    self::FIELD_MODE                => [
                                        self::IS_REQUIRED               => true,
                                        self::DATATYPES                 => [
                                            self::IS_STRING                 => null,
                                        ],
                                    ],
                                    self::FIELD_REASON              => [
                                        self::IS_OPTIONAL               => true,
                                        self::DATATYPES                 => [
                                            self::IS_STRING                 => null,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            self::FIELD_ITEMS               => [
                self::IS_REQUIRED               => true,
                self::DATATYPES                 => [
                    self::IS_ARRAY                  => self::IS_OBJECT,
                ],
                self::CHILDREN                  => [
                    self::IS_REQUIRED               => true,
                    self::HAS_SELECTION             => [
                        self::FIELD_CONTENT_TEXT        => null,
                        self::FIELD_CONTENT_HTML        => null
                    ],
                    self::FIELD_ID                  => [
                        self::IS_REQUIRED               => true,
                        self::DATATYPES                 => [
                            self::IS_NUMERIC                => null,
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_URL                 => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_EXTERNAL_URL        => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_TITLE               => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_CONTENT_HTML        => [
                        self::IS_SELECTION              => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_CONTENT_TEXT        => [
                        self::IS_SELECTION              => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_SUMMARY             => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_IMAGE               => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_BANNER_IMAGE        => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_DATE_PUBLISHED      => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_DATE_MODIFIED       => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_TAGS                => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_ARRAY                  => self::IS_STRING,
                        ],
                    ],
                    self::FIELD_LANGUAGE            => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_ATTACHMENTS         => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_ARRAY                  => self::IS_OBJECT,
                        ],
                        self::CHILDREN                  => [
                            self::IS_REQUIRED               => true,
                            self::FIELD_URL                 => [
                                self::IS_REQUIRED               => true,
                                self::DATATYPES                 => [
                                    self::IS_STRING                 => null,
                                ],
                            ],
                            self::FIELD_MIME_TYPE           => [
                                self::IS_REQUIRED               => true,
                                self::DATATYPES                 => [
                                    self::IS_STRING                 => null,
                                ],
                            ],
                            self::FIELD_TITLE               => [
                                self::IS_OPTIONAL               => true,
                                self::DATATYPES                 => [
                                    self::IS_STRING                 => null,
                                ],
                            ],
                            self::FIELD_SIZE_IN_BYTES       => [
                                self::IS_OPTIONAL               => true,
                                self::DATATYPES                 => [
                                    self::IS_NUMERIC                => null,
                                ],
                            ],
                            self::FIELD_DURATION_IN_SECONDS => [
                                self::IS_OPTIONAL               => true,
                                self::DATATYPES                 => [
                                    self::IS_NUMERIC                => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        self::VERSION_1_0               => [
            self::FIELD_AUTHOR              => [
                self::IS_OPTIONAL               => true,
                self::DATATYPES                 => [
                    self::IS_OBJECT                 => null,
                ],
                self::CHILDREN                  => [
                    self::IS_OPTIONAL               => true,
                    self::FIELD_NAME                => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_URL                 => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_AVATAR              => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                ],
            ],
            self::FIELD_ITEMS               => [
                self::IS_REQUIRED               => true,
                self::DATATYPES                 => [
                    self::IS_ARRAY                  => self::IS_OBJECT,
                ],
                self::CHILDREN                  => [
                    self::FIELD_AUTHOR              => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_OBJECT                 => null,
                        ],
                        self::CHILDREN                  => [
                            self::IS_OPTIONAL               => true,
                            self::FIELD_NAME                => [
                                self::IS_OPTIONAL               => true,
                                self::DATATYPES                 => [
                                    self::IS_STRING                 => null,
                                ],
                            ],
                            self::FIELD_URL                 => [
                                self::IS_OPTIONAL               => true,
                                self::DATATYPES                 => [
                                    self::IS_STRING                 => null,
                                ],
                            ],
                            self::FIELD_AVATAR              => [
                                self::IS_OPTIONAL               => true,
                                self::DATATYPES                 => [
                                    self::IS_STRING                 => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        self::VERSION_1_1               => [
            self::FIELD_AUTHORS             => [
                self::IS_OPTIONAL               => true,
                self::DATATYPES                 => [
                    self::IS_ARRAY                  => self::IS_OBJECT,
                ],
                self::CHILDREN                  => [
                    self::IS_OPTIONAL               => true,
                    self::FIELD_NAME                => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_URL                 => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                    self::FIELD_AVATAR              => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_STRING                 => null,
                        ],
                    ],
                ],
            ],
            self::FIELD_ITEMS               => [
                self::IS_REQUIRED               => true,
                self::DATATYPES                 => [
                    self::IS_ARRAY                  => self::IS_OBJECT,
                ],
                self::CHILDREN                  => [
                    self::IS_OPTIONAL               => true,
                    self::FIELD_AUTHORS             => [
                        self::IS_OPTIONAL               => true,
                        self::DATATYPES                 => [
                            self::IS_ARRAY                 => self::IS_OBJECT,
                        ],
                        self::CHILDREN                  => [
                            self::IS_OPTIONAL               => true,
                            self::FIELD_NAME                => [
                                self::IS_OPTIONAL               => true,
                                self::DATATYPES                 => [
                                    self::IS_STRING                 => null,
                                ],
                            ],
                            self::FIELD_URL                 => [
                                self::IS_OPTIONAL               => true,
                                self::DATATYPES                 => [
                                    self::IS_STRING                 => null,
                                ],
                            ],
                            self::FIELD_AVATAR              => [
                                self::IS_OPTIONAL               => true,
                                self::DATATYPES                 => [
                                    self::IS_STRING                 => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    /**
     * Version
     *
     * Holds the version from the very first tag found
     *
     * @access  private
     * @var     string $version
     */
    private string $version = self::VERSION_1_1;

    /**
     * Context Property
     * 
     * Contains possible a valid set of data for this given feed
     *
     * @access  private
     * @var     array $json
     */
    private array $json = [];

    /**
     * Context Property
     * 
     * Contains sanitized array content.
     * It will be used as the output.
     *
     * @access  private
     * @var     array $context
     */
    private array $context = [];

    /**
     * Magic Construct
     *
     * Creates a file, or replaces and truncates the content of an existing one.
     *
     * @access  public
     * @param   object $object Raw data from source
     * @param   string|array|null $callback **Default `null`**, capture and sanitization callback
     * @return  self
     */
    public function __construct(private object $object, private string|array|null $callback = null)
    {
        \preg_match(self::VERSION_X_X, $object->version ?? '', $matches);

        $this->version = match($matches[2] ?? null) {
            "1"     => self::VERSION_1_0,
            "1.1"   => self::VERSION_1_1,
            default => "",
        };

        $this->setErrorCode(InterfaceErrorCodes::FEED_SOURCE, $this->version === "");
        $this->json = \get_object_vars($object);
        $this->callback ??= [new FeedFacade(), 'capture'];
    }

    /**
     * {@inheritdoc}
     */
    public function getRules(): array
    {
        return self::VERSIONS;
    }

    /**
     * {@inheritdoc}
     */
    public function sanitize(): bool
    {
        if (true === $this->hasErrors()) {
            return false;
        }

        try
        {
            $container = \call_user_func_array($this->callback, [
                $this->getRules()[self::VERSION_X_X],
                $this->json
            ]);

            $this->context = \call_user_func_array($this->callback, [
                $this->getRules()[$this->version],
                $this->json,
                $container
            ]);
        } catch(\Throwable $t) {
            Log::error("{method}. Either validation error, or something else", [
                'method' => __METHOD__,
                'throwable' => $t
            ]);

            $this->setErrorCode(InterfaceErrorCodes::FEED_SANITIZATION);
        }

        return $this->hasErrors() === false;
    }

    /**
     * Accessor
     *
     * Return what has been stored within the context
     *
     * @access  public
     * @return  array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): bool
    {
        return $this->sanitize();
    }
}
