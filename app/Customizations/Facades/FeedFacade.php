<?php

/**
 * Class FeedFacade | ./app/Customizations/Facades/FeedFacade.php
 *
 * Feed Facade class captures downloaded content and uses specific convertor to seed storage medium.
 *
 * @package     Feeds
 * @subpackage  Customizations
 * @author      George Mastrovasilis <george.mastrovasilis@gmail.com>
 * @copyright   Copyright (c) 2023, George Mastrovasilis
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link        https://github.com/gmastro/mindgeektask/tree/master/app/Customizations/Facades/FeedFacade.php
 */

declare(strict_types=1);

namespace App\Customizations\Facades;

use App\Customizations\Components\interfaces\InterfaceContentTypes;
use App\Customizations\Components\Feeds\AtomFeedComponent;
use App\Customizations\Components\Feeds\CsvFeedComponent;
use App\Customizations\Components\Feeds\JsonFeedComponent;
use App\Customizations\Components\Feeds\RdfFeedComponent;
use App\Customizations\Components\Feeds\RssFeedComponent;
use App\Customizations\Components\Feeds\XmlFeedComponent;
use App\Customizations\Composites\DownloadComponent;
use App\Customizations\Proxies\interfaces\InterfaceFeed;
use Illuminate\Support\Arr;

/**
 * Feed Facade
 *
 * Facade pattern performing a set of operations to prepare the data.
 *
 * @category    Facades
 * @package     Feeds
 * @version     0.0.1
 * @todo        Missing multilingual support for all available filetypes
 */
class FeedFacade
{
    /**
     * File Info
     *
     * Extract information from downloaded file
     *
     * @access  public
     * @return  array<string, string|object>
     */
    private function info(DownloadComponent $component): array
    {
        $share = $component->share();
        $extension = \explode('.', $share->filename);

        return [
            'share'         => $share,
            'extension'     => \end($extension),
            'contentType'   => $share->mime_type,
            'mimeType'      => \explode(';', $share->mime_type)[0],
        ];
    }

    /**
     * Converts Content
     *
     * This part behaves as a proxy, it will gather the information from the file and then convert it to the associative
     * handler object.
     *
     * @version 0.0.1
     * @since   0.0.1
     * @access  public
     * @param   DownloadComponent $component
     * @return  InterfaceFeed|null
     */
    public function convertor(DownloadComponent $component): ?InterfaceFeed
    {
        [
            'share'     => $share,
            'extension' => $extension,
            'mimeType'  => $mimeType
        ] = $this->info($component);

        $contentTypes = InterfaceFeed::SUPPORTED[$extension] ?? [];

        if([] === $contentTypes && false === Arr::has($contentTypes, $mimeType)) {
            return null;
        }

        $isPure = (true === Arr::has(InterfaceFeed::PURE, $mimeType)
            && InterfaceFeed::PURE[$mimeType] === $extension);
        
        if (false === $isPure) {
            $mimeType = \array_flip(InterfaceFeed::PURE)[$extension];
        }

        return match($mimeType) {
            InterfaceContentTypes::APPLICATION_JSON                                 => new JsonFeedComponent($share),
            InterfaceContentTypes::APPLICATION_ATOM_XML                             => new AtomFeedComponent($share),
            InterfaceContentTypes::APPLICATION_RSS_XML                              => new RssFeedComponent($share),
            InterfaceContentTypes::APPLICATION_RDF_XML                              => new RdfFeedComponent($share),
            InterfaceContentTypes::TEXT_CSV                                         => new CsvFeedComponent($share),
            InterfaceContentTypes::APPLICATION_XML, InterfaceContentTypes::TEXT_XML => new XmlFeedComponent($share),
            default                                                                 => null,
        };
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
        if(false === Arr::exists($rules, InterfaceFeed::HAS_EXCLUSIVE)) {
            return;
        }

        $intersection = \array_intersect_key($iterator, $rules[InterfaceFeed::HAS_EXCLUSIVE]);
        
        if(\sizeof($intersection) !== 1) {
            throw new \ValueError(\sprintf(
                "Found none of or mutually exclusive keys: [%s]",
                \implode(",", \array_keys($rules[InterfaceFeed::HAS_EXCLUSIVE]))
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
        if(false === Arr::exists($rules, InterfaceFeed::HAS_SELECTION)) {
            return;
        }

        $intersection = \array_intersect_key($iterator, $rules[InterfaceFeed::HAS_SELECTION]);
        if(\sizeof($intersection) < 1) {
            throw new \ValueError(\sprintf(
                "Missing selection of one of available keys: [%s]",
                \implode(",", \array_keys($rules[InterfaceFeed::HAS_SELECTION]))
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
     * @param   mixed $context Validates raw data for possible data type errors
     * @param   array $callables List of callables for validating the context.
     * @return  bool
     * @todo    Possible need to add {@see InterfaceFeed::SET} validation for getting specific values
     */
    private function validate(mixed $context, array $callables): bool
    {
        $result = false;

        foreach($callables as $callable => $inner) {
            try {
                // this is the second level of validation, confirms that the children have the expected structure
                $within = match($inner) {
                    null            => false,
                    InterfaceFeed::IS_OBJECT => $context === \array_filter(
                        $context,
                        fn(mixed $value) => true === \is_array($value) && false === \array_is_list($value)
                    ),
                    default         => $context === \array_filter($context, $inner),
                };

                // this is the first level of validation
                $result |= match($callable) {
                    InterfaceFeed::IS_ARRAY  => \array_is_list($context) && $within,
                    InterfaceFeed::IS_OBJECT => true === \is_array($context) && false === \array_is_list($context),
                    null => true,
                    default => \call_user_func($callable, $context),
                };
            } catch(\TypeError $e) {
                info("{method}. Expected: [{expected}], Got: {got}", [
                    'method'    => __METHOD__,
                    'expected'  => \implode(' -> ', [$callable, $inner]),
                    'got'       => \gettype($context),
                    'context'   => $context,
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
     * @access  public
     * @param   array $mapping Depth selection to verify data integrity
     * @param   array $json Received feed
     * @param   array $container Feed content processed, verified and stored
     * @return  array
     */
    public function capture(array $mapping, array $json, array $container = []): array
    {
        $iterator = \array_diff_key(
            $mapping,
            \array_flip([
                InterfaceFeed::FIELD_VERSION,
                InterfaceFeed::DATATYPES,
                InterfaceFeed::IS_REQUIRED,
                InterfaceFeed::IS_OPTIONAL,
                InterfaceFeed::IS_DEPRECATED,
                InterfaceFeed::HAS_EXCLUSIVE,
                InterfaceFeed::HAS_SELECTION,
                InterfaceFeed::IS_EXCLUSIVE,
                InterfaceFeed::IS_SELECTION,
                InterfaceFeed::SET,
                InterfaceFeed::CHILDREN
            ])
        );

        $this->isExclusive($mapping, $json);
        $this->isSelection($mapping, $json);

        foreach($iterator as $key => $rules) {
            $isRequired = $rules[InterfaceFeed::IS_REQUIRED] ?? false;

            if(false === Arr::exists($json, $key)) {
                if (true === $isRequired) {
                    throw new \ValueError(\sprintf("Required property: `%s` is missing", $key));
                }

                continue;
            }

            // shorthands
            $context = $json[$key];

            if(false === $this->validate($context, $rules[InterfaceFeed::DATATYPES])) {
                if(true === $isRequired) {
                    info("{method}. Validation failure on required property: `{key}`", [
                        'method'    => __METHOD__,
                        'key'       => $key,
                        'rules'     => $rules[InterfaceFeed::DATATYPES],
                        'got'       => \gettype($context),
                        'context'   => $context,
                    ]);

                    throw new \ValueError(\sprintf(
                        "Required property: `%s` has invalid data type or does not satisfy the rules. See log",
                        $key
                    ));
                }

                continue;
            }

            $children = $rules[InterfaceFeed::CHILDREN] ?? [];

            if([] !== $children) {
                if(true === Arr::exists($children, InterfaceFeed::IS_REQUIRED) && [] === $context) {
                    throw new \ValueError(\sprintf("Required property: `%s` exists, yet is empty", $key));
                }

                if(true === Arr::exists($rules[InterfaceFeed::DATATYPES], InterfaceFeed::IS_ARRAY)) {
                    $sizeOfContext = \sizeof($context);
                    for($i = 0; $i < $sizeOfContext; $i++) {
                        $container[$key][$i] = \array_merge(
                            $container[$key][$i] ??= [],
                            $this->capture($children, $context[$i])
                        );
                    }
                } else {
                    $container[$key] = \array_merge(
                        $container[$key] ?? [],
                        $this->capture($children, $context)
                    );
                }

                continue;
            }

            $container[$key] = $context;
        }

        return $container;
    }
}