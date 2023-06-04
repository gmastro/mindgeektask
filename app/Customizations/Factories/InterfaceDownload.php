<?php
declare(strict_types=1);

namespace App\Customizations\Factories;

interface InterfaceDownload extends InterfaceFiles
{
    /**
     * Download Content
     *
     * Try to get content in expected format
     *
     * @access  public
     * @param   null|string $source Url to get information from
     * @return  void
     */
    public function download(string $source = null): void;
}