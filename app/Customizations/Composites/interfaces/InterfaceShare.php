<?php

declare(strict_types=1);

namespace App\Customizations\Composites\interfaces;

use App\Customizations\Components\interfaces\InterfaceComponent;

interface InterfaceShare extends InterfaceComponent
{
    /**
     * Aquire
     *
     * Gets a set of shared values from the previous component, or an initializer in order to process the content.
     *
     * @access  public
     * @param   null|object $shared
     * @return  self
     */
    public function acquire(?object $shared): self;

    /**
     * Share
     *
     * Will return a set of attributes with the next component
     *
     * @access  public
     * @return  null|object
     */
    public function share(): ?object;
}
