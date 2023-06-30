<?php

namespace App\Customizations\Composites;

use App\Customizations\Components\interfaces\InterfaceComponent;
use App\Customizations\Composites\interfaces\InterfaceShare;
use Illuminate\Support\Collection;

class Composite implements InterfaceComponent
{
    /**
     * Magic Construct
     *
     * Starts with a collection of all those classes required to run in sequence and with some shared
     * resources
     */
    public function __construct(private Collection $children, private ?object $attributes = null)
    {
        $this->setChildren($children);
    }

    /**
     * Children Setter
     *
     * Add the collection of components awaiting before processed/executed/run
     *
     * @access  public
     * @param   Collection<int, InterfaceShare> $children A list of component instances
     * @return  void
     */
    public function setChildren(Collection $children): void
    {
        $this->children = $children;
    }

    /**
     * Add Into Collection
     *
     * Remove the component from the end of the collection
     *
     * @access  public
     * @return  InterfaceShare
     */
    public function push(InterfaceShare $component): self
    {
        $this->children->push($component);
        return $this;
    }

    /**
     * Remove From Collection
     *
     * Remove the component from the start of the collection
     *
     * @access  public
     * @return  InterfaceShare
     */
    public function shift(): InterfaceShare
    {
        return $this->children->shift();
    }

    /**
     * Remove From Collection
     *
     * Remove the component from the end of the collection.
     *
     * @access  public
     * @return  InterfaceShare
     */
    public function pop(): InterfaceShare
    {
        return $this->children->pop();
    }

    /**
     * Children Getter
     *
     * Returns the collection of all components which are to be processed
     *
     * @access  public
     * @return  Collection
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     *
     * Execute all components in FIFO manner
     */
    public function execute(): bool
    {
        $response    = true;
        while ($response === true && $this->children->all() !== []) {
            $component  = $this->shift();
            $response  &= $component->acquire($this->attributes)->execute();
            if ($response === true) {
                $this->attributes = $component->share();
            }
        }

        return $response;
    }
}
