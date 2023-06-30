<?php

/**
 * Trait ShareTrait | File ./src/Customizations/Traits/ShareTrait.php
 *
 * Gets and structures variables needed for linking models
 */

declare(strict_types=1);

namespace App\Customizations\Traits;

/**
 * Error Code Trait
 *
 * Will store various error codes in case that something went wrong
 */
trait ShareTrait
{
    /**
     * Acquired Property
     *
     * Holds parameters from previous composite class
     *
     * @access  protected
     * @var     object $acquired
     */
    protected $acquired;

    /**
     * Shared Property
     *
     * Parameters to pass to the next composite class
     *
     * @access  protected
     * @var     array $shared
     */
    protected $shared = [];

    /**
     * {@inheritdoc}
     */
    public function acquire(?object $shared): self
    {
        $this->acquired = $shared;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function share(): ?object
    {
        return $this->shared === [] ? null : (object) $this->shared;
    }

    /**
     * Share Setter
     *
     * Adds or replaces an association within the shared container.
     *
     * @access  protected
     * @param   string $key Name of the property for the association
     * @param   mixed $value Any type of usable value
     * @return  self
     */
    protected function set(string $key, mixed $value): self
    {
        $this->shared[$key] = $value;
        return $this;
    }

    /**
     * Appends/Replaces In Share
     *
     * @access  protected
     * @param   array $shared A batch of key value associations
     * @return  self
     */
    protected function append(array $shared): self
    {
        $this->shared += $shared;
        return $this;
    }

    /**
     * Has Acquired Property
     *
     * Checks if a property already exists within acquired container.
     *
     * @access  protected
     * @param   string $key Name of the property to check
     * @return  bool
     */
    protected function has(string $key): bool
    {
        return \property_exists($this->acquired, $key);
    }

    /**
     * Transfer Into Shared
     *
     * Moves a property from the acquired object into shared container
     *
     * @access  protected
     * @param   string $key Name of the property to transfer
     * @param   null|string $alias Replace the name with a new one
     * @return  self
     */
    protected function transfer(string $key, ?string $alias = null): self
    {
        if ($this->has($key) === true) {
            $alias ??= $key;
            $this->set($alias, $this->acquired->$key);
        }

        return $this;
    }

    /**
     * Unset Shared Value
     *
     * Removes from the shared container a value
     *
     * @access  protected
     * @param   string $key Name of the property to remove
     * @return  self
     */
    protected function unset(string $key): self
    {
        unset($this->shared[$key]);
        return $this;
    }
}
