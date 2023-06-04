<?php
declare(strict_types=1);

namespace App\Customizations\Factories;

use App\Customizations\Factories\InterfaceDownload;
use Illuminate\Support\Facades\File;

class CurlDownload extends AbstractDownload implements InterfaceDownload
{
    /**
     * {@inheritdoc}
     */
    public function download(string $source = null): void
    {
        $ch = \curl_init($source);

        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
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
