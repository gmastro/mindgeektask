<?php
declare(strict_types=1);

namespace App\Customizations\Factories;

use App\Customizations\Factories\InterfaceExaminer;
use Carbon\Carbon;

class GetHeadersExaminer implements InterfaceExaminer
{
    /**
     * Info Property
     *
     * Anything that get_headers will return
     *
     * @access  private
     * @var     null|false|array $info
     */
    private $info;

    private $redirects = 0;

    /**
     * Last Redirect Property
     *
     * In case of possible 301/302 we need only the last response within the info, identifiable by integer key
     *
     * @access  private
     * @var     string $lastRedirect
     */
    private $lastRedirect = '';

    /**
     * Constructor
     */
    public function __construct(string $source)
    {
        $this->build($source);
    }

    /**
     * Retrieve Last Redirect
     *
     * This will be used for getting the status code.
     * There is a chance of prior redirects beforehand, thus, we only need the last response.
     *
     * @access  private
     * @return  void
     */
    private function findLastRedirect(): void
    {
        $redirects = \array_filter($this->info, fn($k) => \is_int($k), ARRAY_FILTER_USE_KEY);
        $last = \sizeof($redirects) - 1;

        $this->redirects = $last;
        $this->lastRedirect = $redirects[$last] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType(): string
    {
        $contentType = $this->info['content-type'] ?? '';

        if(\is_string($contentType)) {
            return $contentType;
        }

        return $contentType[$this->redirects];
    }

    /**
     * {@inheritdoc}
     */
    public function getLastModified(): string
    {
        $lastModified = $this->info['last-modified'] ?? null;
        $lastModified = match($lastModified) {
            null    => Carbon::now(),
            default => Carbon::parse($lastModified),
        };
        
        return $lastModified->toDateTimeString();
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(): bool
    {
        if(\is_array($this->info) === false) {
            return false;
        }

        return \strstr($this->lastRedirect, '200') !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        \preg_match("%(?<statusCode>\d{3}) .*$%", $this->lastRedirect, $matches);
        return (int) ($matches['statusCode'] ?? 0);
    }

    /**
     * {@inheritdoc}
     */
    public function build(string $source): void
    {
        // nasty error masking
        $this->info = @\get_headers($source, true, \stream_context_create(['http' => ['method' => 'HEAD']]));

        if($this->info === false) {
            return;
        }
        
        $keys = \array_map(fn($k) => \is_string($k) ? \strtolower($k) : $k, \array_keys($this->info));
        $values = \array_values($this->info);
        $this->info = \array_combine($keys, $values);

        $this->findLastRedirect();
    }
}