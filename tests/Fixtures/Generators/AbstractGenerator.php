<?php

declare(strict_types=1);

namespace Tests\Fixtures\Generators;

use App\Customizations\Composites\Composite;
use Illuminate\Validation\Rules\Enum;

abstract class AbstractGenerator
{
    /**
     * Order Property
     *
     * Holds the order for which the content will be generated via callbacks.
     *
     * @access  protected
     * @var     iterable $order
     */
    protected iterable $order = [];

    /**
     * Params Property
     *
     * Callbacks to call and parameters to use.
     *
     * @access  protected
     * @var     iterable $params
     */
    protected iterable $params = [];

    /**
     * Composite Property
     *
     * It will use all those components needed to generate the content.
     *
     * > **Note**:  Both the composite and the components are tested classes
     */
    protected Composite $composite;

    abstract protected function isAllowedEnumerator(): void;

    abstract protected function mapping(int $action): bool;

    /**
     * Magic Construct
     *
     * Declaration of the enumeration with the available actions.
     * Additionally, initializes data and flags.
     * The latter will determine the order of the generated content.
     *
     * @access  public
     * @param   Enum $enumerator
     * @param   iterable $data **Default `[]`**
     * @param   int $flags **Default `0`**
     */
    public function __construct(protected Enum $enumerator, protected iterable $data = [], protected int $flags = 0)
    {
        $this->isAllowedEnumerator();
    }

    /**
     * Accessor
     * 
     * Sets or replaces some additional data.
     *
     * @access  public
     * @param   string $key
     * @param   mixed $content
     * @return  static
     */
    public function pushData(string $key, mixed $content): static
    {
        $this->data[$key] = $content;
        return $this;
    }

    /**
     * Accessor
     * 
     * Defines which flags will be used to extract content from the provided params.
     * For more details related to params please {@see AbstractGenerator::execute()}
     *
     * @access  public
     * @param   int $flags
     * @return  static
     */
    public function setFlags(int $flags): static
    {
        if($flags < 1) {
            throw new \Exception("Flags must be greater equal to `1`");
        }

        $this->flags = $flags;
        return $this;
    }

    /**
     * Accessor
     * 
     * Sets those params containing the name of the action to use, possible a callable and the arguments of the callable
     *
     * @access  public
     * @param   iterable<string $key, mixed[]> $params
     * @return  static
     */
    public function setParams(iterable $params): static
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Accessor
     *
     * Returns the data
     * 
     * @access  public
     * @return  iterable
     */
    public function getData(): iterable
    {
        return $this->data;
    }

    /**
     * Actions Order
     * 
     * Sets the order of actions which will determine the generated content.
     *
     * > **Note**:  The enumeration flag values **MUST** be integers shifted from LSB to MSB
     *
     * @access  public
     * @return  static
     */
    public function order(): static
    {
        for($i = 1; $i <= $this->flags; $this->mapping($this->flags & $i), $i << 1);

        return $this;
    }

    /**
     * Execute
     */
    public function execute(): bool
    {
        if ([] === $this->order) {
            return false;
        }

        $this->composite = new Composite(collect($this->order), (object) $this->params);
        return $this->composite->execute();
    }
}