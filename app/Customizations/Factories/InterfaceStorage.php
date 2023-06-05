<?php
declare(strict_types=1);

namespace App\Customizations\Factories;

interface InterfaceStorage
{
    /**
     * Store Content
     *
     * Return the status code response from a performed a HEAD request
     *
     * @access  public
     * @param   string $source Url to get information from
     * @return  string|bool
     */
    public function store(string $source): string|bool;
}