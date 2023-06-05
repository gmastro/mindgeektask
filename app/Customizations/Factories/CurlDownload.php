<?php
declare(strict_types=1);

namespace App\Customizations\Factories;

use App\Customizations\Factories\InterfaceDownload;
use Illuminate\Support\Facades\File;

class CurlDownload extends AbstractDownload implements InterfaceDownload
{
    private $curlOptions = [];

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
    public function setCurlOptions(array $curlOptions = []): self
    {
        $this->curlOptions = $curlOptions;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function download(string $source = null): void
    {
        $ch = \curl_init($source);

        \curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => false] + $this->curlOptions);

        $this->contents = \curl_exec($ch);

        if(\curl_errno($ch) !== 0) {
            \curl_close($ch);
            return;
        }

        \curl_close($ch);

        if($this->contents !== false && $this->disk->put($this->path, $this->contents)) {
            $this->setExtension();
        }
    }
}
