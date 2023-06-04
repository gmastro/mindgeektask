<?php
declare(strict_types=1);

namespace App\Customizations\Factories;

use App\Customizations\Factories\InterfaceDownload;

class FileGetContentsDownload extends AbstractDownload implements InterfaceDownload
{
    /**
     * Context Options Property
     *
     * A stream resource to use for downloading content
     *
     * @access  private
     * @var     mixed $contents
     */
    private $contextOptions;

    /**
     * Context Options Setter
     *
     * Adds the intruction set right after received via the examiner.
     * This way you may store the content in binary format, rather than string
     *
     * @access  public
     * @param   mixed $options Either resource, iterable, or array. For anything else it will be null
     * @return  self
     */
    public function setContextOptions(mixed $options): self
    {
        $this->contextOptions = match(\gettype($options)) {
            'iterable'  => \stream_context_create(\iterator_to_array($options)),
            'array'     => \stream_context_create($options),
            'resource'  => \get_resource_type($options) === 'stream' ? $options: null,
            default     => null,
        };

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function download(string $source = null): void
    {
        $source ??= $this->source;
        $this->contents = \file_get_contents($source, true, $this->contextOptions);
        
        if($this->contents !== false && $this->disk->put($this->path, $this->contents)) {
            $this->setExtension();
        }
    }
}
