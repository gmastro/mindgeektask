<?php

declare(strict_types=1);

namespace App\Customizations\Components\interfaces;

interface InterfaceComponent
{
    /**
     * Perform Operation
     *
     * With the given component instance and after all information is gathered, perform an operation
     *
     * @access  public
     * @return  bool
     */
    public function execute(): bool;
}
