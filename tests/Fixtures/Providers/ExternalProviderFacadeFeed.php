<?php

declare(strict_types=1);

namespace Tests\Fixtures\Providers;

use App\Customizations\Components\JsonFeed;

final class ExternalProviderFacadeFeed
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
        $array = ExternalProviderJsonFeed::providerSuccessJson();

        foreach($array as $key => &$value) {
            $value[] = JsonFeed::class;
            $value[] = JsonFeed::VERSION_X_X;
        }

        return $array;
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
        $array = ExternalProviderJsonFeed::providerFailureJson();

        foreach($array as $key => &$value) {
            $value[] = JsonFeed::class;
            $value[] = JsonFeed::VERSION_X_X;
        }

        return $array;
    }

    /**
     * Data Provider
     *
     * Usable data for SUTs, STUBs and MOCKs
     * Constructor exception data with rules to use
     *
     * @access  public
     * @static
     * @return  array<string, array<int, object>>
     */
    public static function providerExceptionJson(): array
    {
        $array = ExternalProviderJsonFeed::providerExceptionJson();

        foreach($array as &$value) {
            $value[] = JsonFeed::class;
            $value[] = JsonFeed::VERSION_X_X;
        };

        return $array;
    }
}