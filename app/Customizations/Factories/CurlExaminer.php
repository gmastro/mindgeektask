<?php
declare(strict_types=1);

namespace App\Customizations\Factories;

use App\Customizations\Factories\InterfaceExaminer;
use Carbon\Carbon;

class CurlExaminer implements InterfaceExaminer
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

    /**
     * Constructor
     */
    public function __construct(string $source)
    {
        $this->build($source);
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType(): string
    {
        return $this->info['content_type'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function getLastModified(): string
    {
        $lastModified = $this->info['filetime'] ?? null;
        $lastModified = match($lastModified) {
            null    => Carbon::now(),
            default => Carbon::createFromTimestamp($lastModified),
        };
        
        return $lastModified->toDateTimeString();
    }

    /**
     * {@inheritdoc}
     */
    public function isValid(): bool
    {
        return $this->getStatusCode() === 200;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return (int) ($this->info['http_code'] ?? 0);
    }

    /**
     * {@inheritdoc}
     */
    public function build(string $source): void
    {
        $ch = \curl_init($source);
        \curl_setopt($ch, CURLOPT_NOBODY, true);
        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($ch, CURLOPT_FILETIME, true);
        
        if(\curl_exec($ch) === false || \curl_errno($ch)) {
            return;
        }

        $this->info = \curl_getinfo($ch);
        \curl_close($ch);
    }
}