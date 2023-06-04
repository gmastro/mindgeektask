<?php

namespace App\Customizations\Composites;

use Illuminate\Support\Collection;

class RemoteFeedComposite implements InterfaceComponent
{
    /**
     * Children Property
     *
     * All component instances required to be be performed sequencially
     *
     * @access  private
     * @var     Collection<int, InterfaceComponent> $children
     */
    private $children;

    /**
     * Handles create
     * - 
     */
    public function __construct(Collection $children)
    {
        $this->setChildren($children);
    }

    /**
     * Children Setter
     *
     * Add the collection of components awaiting before processed/executed/run
     *
     * @access  public
     * @param   Collection $children A list of component instances
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
     * @return  InterfaceComponent
     */
    public function push(InterfaceComponent $component): self
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
     * @return  InterfaceComponent
     */
    public function shift(): InterfaceComponent
    {
        return $this->children->shift();
    }

    /**
     * Remove From Collection
     *
     * Remove the component from the end of the collection.
     *
     * @access  public
     * @return  InterfaceComponent
     */
    public function pop(): InterfaceComponent
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
        $response = true;
        while($response === true && $this->children->all() !== []) {
            try {
                $component = $this->shift();
                $response &= $component->execute();
            } catch(\Throwable $e) {
                $response = false;
            }
        }

        return $response;
    }
}
