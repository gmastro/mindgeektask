<?php

declare(strict_types=1);

namespace App\Customizations\Composites;

use App\Customizations\Composites\interfaces\InterfaceComposite;
use App\Customizations\Composites\interfaces\InterfaceShare;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use UnhandledMatchError;

class Composite implements InterfaceComposite
{
    /**
     * Current Property
     *
     * Node shifted or poped from collection.
     * Usable for tracking active node state
     *
     * @access  private
     * @var     null|InterfaceShare $current
     */
    private $current;

    /**
     * Mode Property
     *
     * Either queue or stack. Default starting value `queue`
     *
     * @access  private
     * @var     string $mode
     */
    private $mode = 'queue';

    /**
     * Magic Construct
     *
     * Starts with a collection of all those classes required to run in sequence and with some shared
     * resources
     *
     * @access  public
     * @param   Collection $children Instances to process
     * @param   null|object $attributes **Default `null`**, attributes initializer and container for any data shared
     *          between children
     */
    public function __construct(private Collection $children, private ?object $attributes = null)
    {
        // nothing here
    }

    /**
     * Children Setter
     *
     * Add the collection of components awaiting before processed/executed/run
     *
     * @access  public
     * @param   Collection<int, InterfaceShare> $children A list of component instances
     * @return  self
     */
    public function setChildren(Collection $children): self
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Mode Setter
     *
     * Use one of the available modes for creating the composite.
     * Available modes are queue or stack
     *
     * @access  public
     * @param   string $mode
     * @return  self
     */
    public function setMode(string $mode): self
    {
        $mode = match ($mode) {
            'queue', 'stack'    => $mode,
            default             => throw new InvalidArgumentException("Available modes [queue|stack]"),
        };

        $this->mode = $mode;
        return $this;
    }

    /**
     * Current Node Setter
     *
     * Holds currently active node.
     * Mainly used for testing to determine the set of data already used and stored within active node.
     *
     * @access  private
     * @param   InterfaceShare $current Active node
     * @return  self
     */
    private function setCurrent(InterfaceShare $current): self
    {
        $this->current = $current;
        return $this;
    }

    /**
     * Current Node Getter
     *
     * Will return current active node.
     * In case that
     *
     * @access  public
     * @return  null|InterfaceShare
     */
    public function getCurrent(): ?InterfaceShare
    {
        return $this->current;
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
        $this->setCurrent($this->children->shift());
        return $this->getCurrent();
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
        $this->setCurrent($this->children->pop());
        return $this->getCurrent();
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
     * Process all components in FIFO (queue mode) or LIFO (stack mode)
     */
    public function execute(): bool
    {
        $response    = true;

        $shiftOrPop  = match ($this->mode) {
            'queue' => 'shift',
            'stack' => 'pop',
            default => throw new UnhandledMatchError("Available modes [queue|stack]"),
        };

        while ($response === true && $this->children->all() !== []) {
            $response = $this->$shiftOrPop()->acquire($this->attributes)->execute();
            $this->attributes = $this->getCurrent()->share();
        }

        return $response;
    }
}
