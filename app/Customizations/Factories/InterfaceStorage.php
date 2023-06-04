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
     * @return  string|bool
     */
    public function store(): string|bool;
}