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

use App\Customizations\Proxies\interfaces\InterfaceFeed;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use LogicException;

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
                self::IS_REQUIRED,
            ],
            self::FIELD_TITLE               => [
                self::IS_REQUIRED,
            ],
            self::FIELD_ICON                => [
                self::IS_OPTIONAL,
            ],
            self::FIELD_HOME_PAGE_URL       => [
                self::IS_OPTIONAL,
            ],
            self::FIELD_FEED_URL            => [
                self::IS_OPTIONAL,
            ],
            self::FIELD_DESCRIPTION         => [
                self::IS_OPTIONAL,
            ],
            self::FIELD_USER_COMMENT        => [
                self::IS_OPTIONAL,
            ],
            self::FIELD_NEXT_URL            => [
                self::IS_OPTIONAL,
            ],
            self::FIELD_ICON                => [
                self::IS_OPTIONAL,
            ],
            self::FIELD_FAVICON             => [
                self::IS_OPTIONAL,
            ],
            // authors (moved in version 1.1)
            self::FIELD_LANGUAGE            => [
                self::IS_OPTIONAL,
            ],
            self::FIELD_EXPIRED             => [
                self::IS_OPTIONAL,
            ],
            self::FIELD_HUBS            => [
                self::IS_OPTIONAL,
                self::CHILDREN              => [
                    self::FIELD_URL             => [
                        self::IS_REQUIRED,
                    ],
                    self::FIELD_TYPE                => [
                        self::IS_REQUIRED,
                        self::CHILDREN              => [
                            "WebSub"                    => [
                                self::IS_REQUIRED,
                                self::CHILDREN              => [
                                    self::FIELD_MODE            => [
                                        self::IS_REQUIRED,
                                    ],
                                    self::FIELD_REASON          => [
                                        self::IS_OPTIONAL,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            self::FIELD_ITEMS               => [
                self::IS_REQUIRED,
                self::CHILDREN                  => [
                    self::FIELD_ID                  => [
                        self::IS_REQUIRED,
                    ],
                    self::FIELD_URL                 => [
                        self::IS_OPTIONAL,
                    ],
                    self::FIELD_EXTERNAL_URL        => [
                        self::IS_OPTIONAL,
                    ],
                    self::FIELD_TITLE               => [
                        self::IS_OPTIONAL,
                    ],
                    self::FIELD_CONTENT_HTML        => [
                        self::IS_SELECTION,
                        self::SET                       => [
                            self::FIELD_CONTENT_TEXT        => null
                        ],
                    ],
                    self::FIELD_CONTENT_TEXT        => [
                        self::IS_SELECTION,
                        self::SET                       => [
                            self::FIELD_CONTENT_HTML        => null
                        ],
                    ],
                    self::FIELD_SUMMARY             => [
                        self::IS_OPTIONAL,
                    ],
                    self::FIELD_IMAGE               => [
                        self::IS_OPTIONAL,
                    ],
                    self::FIELD_BANNER_IMAGE        => [
                        self::IS_OPTIONAL,
                    ],
                    self::FIELD_DATE_PUBLISHED      => [
                        self::IS_OPTIONAL,
                    ],
                    self::FIELD_DATE_MODIFIED       => [
                        self::IS_OPTIONAL,
                    ],
                    self::FIELD_TAGS                => [
                        self::IS_OPTIONAL,
                    ],
                    self::FIELD_LANGUAGE            => [
                        self::IS_OPTIONAL,
                    ],
                    self::FIELD_ATTACHMENTS         => [
                        self::IS_OPTIONAL,
                        self::CHILDREN                  => [
                            self::FIELD_URL                 => [
                                self::IS_REQUIRED,
                            ],
                            self::FIELD_MIME_TYPE           => [
                                self::IS_REQUIRED,
                            ],
                            self::FIELD_TITLE               => [
                                self::IS_OPTIONAL,
                            ],
                            self::FIELD_SIZE_IN_BYTES       => [
                                self::IS_OPTIONAL,
                            ],
                            self::FIELD_DURATION_IN_SECONDS => [
                                self::IS_OPTIONAL,
                            ],
                        ],
                    ],
                ],
            ],
        ],
        self::VERSION_1_0           => [
            self::FIELD_AUTHOR          => [
                self::IS_OPTIONAL,
                self::CHILDREN              => [
                    self::IS_OPTIONAL,
                    self::FIELD_NAME            => [
                        self::IS_OPTIONAL,
                    ],
                    self::FIELD_URL             => [
                        self::IS_OPTIONAL,
                    ],
                    self::FIELD_AVATAR          => [
                        self::IS_OPTIONAL,
                    ],
                ],
            ],
            self::FIELD_ITEMS       => [
                self::IS_REQUIRED,
                self::CHILDREN          => [
                    self::FIELD_AUTHOR          => [
                        self::IS_OPTIONAL,
                        self::CHILDREN              => [
                            self::IS_OPTIONAL,
                            self::FIELD_NAME            => [
                                self::IS_OPTIONAL,
                            ],
                            self::FIELD_URL             => [
                                self::IS_OPTIONAL,
                            ],
                            self::FIELD_AVATAR          => [
                                self::IS_OPTIONAL,
                            ],
                        ],
                    ],
                ],
            ],
        ],
        self::VERSION_1_1           => [
            self::FIELD_AUTHORS         => [
                self::IS_OPTIONAL,
                self::CHILDREN              => [
                    self::FIELD_NAME            => [
                        self::IS_OPTIONAL,
                    ],
                    self::FIELD_URL             => [
                        self::IS_OPTIONAL,
                    ],
                    self::FIELD_AVATAR          => [
                        self::IS_OPTIONAL,
                    ],
                ],
            ],
            self::FIELD_ITEMS       => [
                self::IS_REQUIRED,
                self::CHILDREN          => [
                    self::FIELD_AUTHORS         => [
                        self::IS_OPTIONAL,
                        self::CHILDREN              => [
                            self::FIELD_NAME            => [
                                self::IS_OPTIONAL,
                            ],
                            self::FIELD_URL             => [
                                self::IS_OPTIONAL,
                            ],
                            self::FIELD_AVATAR          => [
                                self::IS_OPTIONAL,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    /**
     * Data Types
     *
     * What data type to expect from the each field
     *
     * @access  public
     * @static
     * @var     array<string, array> DATATYPES
     */
    public const DATATYPES = [
        self::FIELD_ATTACHMENTS         => ['object[]'],
        self::FIELD_AUTHOR              => ['object[]'],
        self::FIELD_AUTHORS             => ['object[]'],
        self::FIELD_AVATAR              => ['string'],
        self::FIELD_BANNER_IMAGE        => ['string'],
        self::FIELD_CONTENT_HTML        => ['string'],
        self::FIELD_CONTENT_TEXT        => ['string'],
        self::FIELD_DATE_MODIFIED       => ['string'],
        self::FIELD_DATE_PUBLISHED      => ['string'],
        self::FIELD_DESCRIPTION         => ['string'],
        self::FIELD_DURATION_IN_SECONDS => ['numeric'],
        self::FIELD_EXPIRED             => ['bool'],
        self::FIELD_EXTERNAL_URL        => ['string'],
        self::FIELD_FAVICON             => ['string'],
        self::FIELD_FEED_URL            => ['string'],
        self::FIELD_HOME_PAGE_URL       => ['string'],
        self::FIELD_HUBS                => ['object[]'],
        self::FIELD_ICON                => ['string'],
        self::FIELD_ID                  => ['string'],
        self::FIELD_IMAGE               => ['string'],
        self::FIELD_ITEMS               => ['object[]'],
        self::FIELD_LANGUAGE            => ['string'],
        self::FIELD_MIME_TYPE           => ['string'],
        self::FIELD_MODE                => ['string', self::SET => ['subscribe', 'unsubscribe']],
        self::FIELD_NAME                => ['string'],
        self::FIELD_NEXT_URL            => ['string'],
        self::FIELD_REASON              => ['string'],
        self::FIELD_SIZE_IN_BYTES       => ['numeric'],
        self::FIELD_SUMMARY             => ['string'],
        self::FIELD_TAGS                => ['string[]'],
        self::FIELD_TITLE               => ['string'],
        self::FIELD_TYPE                => ['string', self::SET => ["WebSub", "rssCloud"]],
        self::FIELD_URL                 => ['string'],
        self::FIELD_USER_COMMENT        => ['string'],
        self::FIELD_VERSION             => ['string'],
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
     * Flag Property
     *
     * Whether the given feed is valid or not, it will be invalid until processed.
     *
     * @access  private
     * @var     bool $isValid
     */
    private bool $isValid = false;

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
     * Magic Construct
     *
     * Creates a file, or replaces and truncates the content of an existing one.
     *
     * @access  public
     * @param   string|null $filename Location to read/write from
     * @param   string $mode Flags to determine the type of access for the given resource
     * @return  self
     */
    public function __construct(private object $object)
    {
        $version = $object->version ?? '';
        
        if(false === preg_match(self::VERSION_X_X, $version, $matches)) {
            throw new LogicException(preg_last_error_msg());
        }

        $this->version = match($matches[2] ?? null) {
            "1"     => self::VERSION_1_0,
            "1.1"   => self::VERSION_1_1,
            default => "",
        };

        $this->isValid = $this->version !== "";
        $this->json = \get_object_vars($object);
    }

    /**
     * Recursive Capture
     *
     * Will check through all the available fields if the set of conditions is matched
     * Oncy they do it will store the content.
     *
     * @access  private
     * @param   array $mapping Depth selection to verify data integrity
     * @param   array $json Received feed
     * @param   array $container Feed content processed, verified and stored
     */
    private function capture(array $mapping, array $json, array $container = []): array
    {
        $iterator = \array_diff_key(
            $mapping,
            \array_flip([
                self::FIELD_VERSION,
                self::IS_REQUIRED,
                self::IS_OPTIONAL,
                self::IS_DEPRECATED,
                self::IS_EXCLUSIVE,
                self::IS_SELECTION,
                self::CHILDREN
            ])
        );

        foreach($iterator as $key => $rules) {
            if(false === Arr::exists($json, $key)) {
                if (true === Arr::exists($mapping, self::IS_REQUIRED)) {
                    throw new \ValueError(\sprintf("Required property: `%s` is missing", $key));
                }

                continue;
            }

            // shorthands
            $context = $json[$key];

            if(true === Arr::exists($mapping, self::CHILDREN)) {
                if(false === \is_array($context)) {
                    throw new \ValueError(\sprintf("Expected iterable, instead got %s", \gettype($context)));
                }

                if(true === Arr::exists($mapping[self::CHILDREN], self::IS_REQUIRED) && true === empty($context)) {
                    throw new \ValueError(\sprintf("Required property: `%s` exists, yet is empty", $key));
                }

                $container[$key] = $this->capture($mapping[self::CHILDREN], $context);
                continue;
            }

            $container[$key] = $context;
        }

        return $container;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): bool
    {
        if (false === $this->isValid) {
            return $this->isValid;
        }

        $container = $this->capture(self::VERSIONS[self::VERSION_X_X], $this->json);
        $container = $this->capture(self::VERSIONS[$this->version], $this->json, $container);

        dump($container);

        return true;
    }
}
