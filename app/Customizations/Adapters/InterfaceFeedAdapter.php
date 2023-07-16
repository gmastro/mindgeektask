<?php

declare(strict_types=1);

namespace App\Customizations\Adapters;

interface InterfaceFeedAdapter
{
    /**
     * Processe Data
     *
     * From a given source it will attempt to receive data.
     * Once the data is received it will be processed
     *
     * @access  public
     * @param   null|string $source Url or path to get information from
     * @return  void
     */
    public function process(string $source = null): void;
}
