<?php

/**
 * Class RssFeed | ./app/Customizations/Components/RssFeed.php
 *
 * Prepares the content for storage or in case of exclusive defined structure returns the content and process it as is.
 *
 * @package     Feeds
 * @subpackage  Customizations
 * @author      George Mastrovasilis <george.mastrovasilis@gmail.com>
 * @copyright   Copyright (c) 2023, George Mastrovasilis
 * @license     https://www.gnu.org/licenses/gpl-3.0.txt GNU/GPLv3
 * @link        https://github.com/gmastro/mindgeektask/tree/master/app/Customizations/Components/RssFeed.php
 */

declare(strict_types=1);

namespace App\Customizations\Components;

use App\Customizations\Components\interfaces\InterfaceErrorCodes;
use App\Customizations\Proxies\interfaces\InterfaceFeed;
use App\Customizations\Traits\ErrorCodeTrait;
use Illuminate\Support\Facades\Log;

/**
 * Rss Feed
 *
 * Some Description
 *
 * @category    Components
 * @package     Feeds
 * @version     0.0.1
 * @todo        Missing description
 */
class RssFeed implements InterfaceFeed
{
    use ErrorCodeTrait;

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
    public const VERSIONS = [];

    /**
     * Version
     *
     * Holds the version from the very first tag found
     *
     * @access  private
     * @var     string $version
     */
    private string $version = 'version';

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
     * From provided object captures the content and converts it to an associative array.
     *
     * @access  public
     * @param   object $object Raw data from source
     * @param   string|array|null $callback **Default `null`**, capture and sanitization callback
     * @return  self
     */
    public function __construct(private object $object, private string|array|null $callback = null)
    {
        // do something
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
                $this->getRules()['version'],
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
