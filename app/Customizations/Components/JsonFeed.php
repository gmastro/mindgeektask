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
     * @param   string|null $filename Location to read/write from
     * @param   string $mode Flags to determine the type of access for the given resource
     * @return  self
     */
    public function __construct(private object $object)
    {
        $version = $object->version ?? '';
        
        \preg_match(self::VERSION_X_X, $version, $matches);

        $this->version = match($matches[2] ?? null) {
            "1"     => self::VERSION_1_0,
            "1.1"   => self::VERSION_1_1,
            default => "",
        };

        $this->isValid = $this->version !== "";
        $this->json = \get_object_vars($object);
    }

    /**
     * Has Exclusive
     *
     * Verify whether the iterator contains exclusive fields.
     * Only one must be present
     *
     * @access  private
     * @param   array $rules Rules applied for parent field
     * @param   array $iterator Present fields to examine
     * @return  void
     */
    private function isExclusive(array $rules, array $iterator): void
    {
        if(false === Arr::exists($rules, self::HAS_EXCLUSIVE)) {
            return;
        }

        $intersection = \array_intersect_key($iterator, $rules[self::HAS_EXCLUSIVE]);
        
        if(\sizeof($intersection) !== 1) {
            throw new \ValueError(\sprintf(
                "Found mutually exclusive keys: [%s]",
                \implode(",", \array_keys($rules[self::HAS_EXCLUSIVE]))
            ));
        }
    }

    /**
     * Has Selection
     *
     * Verify whether the iterator contains selection fields.
     * At least a single one has to be available.
     *
     * @access  private
     * @param   array $rules Rules applied for parent field
     * @param   array $iterator Present fields to examine
     * @return  void
     */
    private function isSelection(array $rules, array $iterator): void
    {
        if(false === Arr::exists($rules, self::HAS_SELECTION)) {
            return;
        }

        $intersection = \array_intersect_key($iterator, $rules[self::HAS_SELECTION]);
        if(\sizeof($intersection) < 1) {
            throw new \ValueError(\sprintf(
                "Missing selection of one of available keys: [%s]",
                \implode(",", \array_keys($rules[self::HAS_SELECTION]))
            ));
        }
    }

    /**
     * Validator
     *
     * Checks if the content within each and every node holds expected datatype content.
     * Since there are might be more than a single validating cases the iterator will stop on the very first true case.
     *
     * @access  private
     * @param   mixed $context The values to 
     */
    private function validate(mixed $context, array $callables): bool
    {
        $result = false;

        foreach($callables as $callable => $inner) {
            try {
                $within = match($inner) {
                    null            => false,
                    self::IS_OBJECT => $context === \array_filter(
                        $context,
                        fn(mixed $value) => \is_array($value) && false === \array_is_list($value)
                    ),
                    default         => $context === \array_filter($context, $inner),
                };

                $result |= match($callable) {
                    self::IS_ARRAY  => \array_is_list($context) && $within,
                    null            => true,
                    default         => \call_user_func($callable, $context),
                };
            } catch(\TypeError $e) {
                info("{method}. Expected: [{expected}], Got: {got}", [
                    'method'    => __METHOD__,
                    'expected'  => \implode(' -> ', [$callable, $inner]),
                    'got'       => \gettype($context)
                ]);
            }

            $result = (bool) $result;

            if(true === $result) {
                return $result;
            }
        }

        return $result;
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
     * @return  array
     */
    private function capture(array $mapping, array $json, array $container = []): array
    {
        $iterator = \array_diff_key(
            $mapping,
            \array_flip([
                self::FIELD_VERSION,
                self::DATATYPES,
                self::IS_REQUIRED,
                self::IS_OPTIONAL,
                self::IS_DEPRECATED,
                self::HAS_EXCLUSIVE,
                self::HAS_SELECTION,
                self::IS_EXCLUSIVE,
                self::IS_SELECTION,
                self::SET,
                self::CHILDREN
            ])
        );

        $this->isExclusive($mapping, $json);
        $this->isSelection($mapping, $json);

        foreach($iterator as $key => $rules) {
            if(false === Arr::exists($json, $key)) {
                if (true === Arr::exists($rules, self::IS_REQUIRED)) {
                    throw new \ValueError(\sprintf("Required property: `%s` is missing", $key));
                }

                continue;
            }

            // shorthands
            $context = $json[$key];

            if(false === $this->validate($context, $rules[self::DATATYPES])) {
                if(true === Arr::exists($rules, self::IS_REQUIRED)) {
                    info("{method}. Validation failure on required property: `{key}`", [
                        'method'    => __METHOD__,
                        'key'       => $key,
                        'rules'     => $rules[self::DATATYPES],
                        'context'   => $context,
                    ]);

                    throw new \ValueError(\sprintf(
                        "Required property: `%s` has invalid data type or does not satisfy the rules. See log",
                        $key
                    ));
                }

                continue;
            }

            if(true === Arr::exists($rules, self::CHILDREN)) {
                if(true === Arr::exists($rules[self::CHILDREN], self::IS_REQUIRED) && [] === $context) {
                    throw new \ValueError(\sprintf("Required property: `%s` exists, yet is empty", $key));
                }

                if(true === Arr::exists($rules[self::DATATYPES], self::IS_ARRAY)) {
                    $sizeOfContext = \sizeof($context);
                    for($i = 0; $i < $sizeOfContext; $i++) {
                        $container[$key][$i] = \array_merge(
                            $container[$key][$i] ??= [],
                            $this->capture($rules[self::CHILDREN], $context[$i])
                        );
                    }
                } else {
                    $container[$key] = \array_merge(
                        $container[$key] ?? [],
                        $this->capture($rules[self::CHILDREN], $context)
                    );
                }

                continue;
            }

            $container[$key] = $context;
        }

        return $container;
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
        if (false === $this->isValid) {
            return $this->isValid;
        }

        $container = $this->capture(self::VERSIONS[self::VERSION_X_X], $this->json);
        $this->context = $this->capture(self::VERSIONS[$this->version], $this->json, $container);

        return true;
    }
}
