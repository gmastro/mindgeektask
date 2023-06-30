<?php

declare(strict_types=1);

namespace Tests\Unit\Customizations\Composites;

use App\Customizations\Composites\Composite;
use App\Customizations\Composites\ExamineComponent;
use App\Customizations\Composites\interfaces\InterfaceComposite;
use App\Customizations\Composites\interfaces\InterfaceShare;
use App\Customizations\Traits\ShareTrait;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use UnhandledMatchError;

#[CoversClass(Composite::class)]
#[UsesClass(ExamineComponent::class)]
#[UsesClass(ShareTrait::class)]
class CompositeTest extends TestCase
{
    // public static function providerUrlsResults(): array
    // {
    //     return \array_map(fn($u, $r) => \array_merge($u, $r), self::providerUrls(), self::providerResults());
    // }

    public static function providerEmpty(): array
    {
        return [
            'empty-1'   => [collect([])],
            'empty-2'   => [collect([]), null],
            'empty-3'   => [collect([]), (object) ['data' => 42]],
        ];
    }

    #[Group('success')]
    #[Group('constructor')]
    #[Group('empty')]
    #[DataProvider('providerEmpty')]
    public function test_success_empty_constructor(): void
    {
        $sut = new Composite(...\func_get_args());
        $this->assertInstanceOf(InterfaceComposite::class, $sut);
        $this->assertSame(0, $sut->getChildren()->count());
        $this->assertTrue($sut->execute());
    }

    private static function generateChild(bool $willReturn = true, ?object $data = null): InterfaceShare
    {
        return new class ($willReturn, $data) implements InterfaceShare {
            use ShareTrait;
            public $uuid;
            public function __construct(private bool $response, private ?object $data = null)
            {
                $this->uuid = fake()->uuid();
            }
            public function execute(): bool
            {
                if ($this->has('data') || $this->data !== null) {
                    $this->set('data', $this->has('data') ? ++$this->acquired->data : ++$this->data->data);
                }
                return $this->response;
            }
        };
    }

    #[Group('success')]
    #[Group('push')]
    #[Group('execute')]
    #[DataProvider('providerEmpty')]
    public function test_success_push(): void
    {
        $child = $this->generateChild();
        $sut = new Composite(...\func_get_args());
        $sut->push($child);

        $this->assertSame(1, $sut->getChildren()->count());
        $this->assertTrue($sut->execute());
        $this->assertSame(0, $sut->getChildren()->count());
    }

    #[Group('failure')]
    #[Group('push')]
    #[Group('execute')]
    #[DataProvider('providerEmpty')]
    public function test_failure_push(): void
    {
        $child = $this->generateChild(false);
        $sut = new Composite(...\func_get_args());
        $sut->push($child);

        $this->assertSame(1, $sut->getChildren()->count());
        $this->assertFalse($sut->execute());
        $this->assertSame(0, $sut->getChildren()->count());
    }

    public static function providerChildren(): array
    {
        $map = fn (int $i, ?object $data = null) => \array_map(fn () => self::generateChild(true, $data), range(1, $i));
        return [
            'children-no-data'      => [collect($map(5)), null, null],
            'children-init-data'    => [collect($map(5)), (object) ['data' => 42], 47],
            'children-inner-data'   => [collect($map(5, (object) ['data' => 666])), null, 671],
        ];
    }

    #[Group('success')]
    #[Group('set')]
    #[Group('queue')]
    #[Group('execute')]
    #[DataProvider('providerChildren')]
    public function test_success_set_children_queue_mode(Collection $collection, ?object $attributes, ?int $expected): void
    {
        $sut = new Composite(collect(), $attributes);
        $this->assertSame(0, $sut->getChildren()->count());

        $sut->setMode('queue')->setChildren($collection);
        $this->assertSame(5, $sut->getChildren()->count());

        $this->assertTrue($sut->execute());
        $this->assertSame(0, $sut->getChildren()->count());
        $this->assertInstanceOf(InterfaceShare::class, $sut->getCurrent());

        $shared = $sut->getCurrent()->share();
        $this->assertSame($expected, $shared->data ?? null);
    }

    #[Group('success')]
    #[Group('set')]
    #[Group('stack')]
    #[Group('execute')]
    #[DataProvider('providerChildren')]
    public function test_success_set_children_stack_mode(Collection $collection, ?object $attributes, ?int $expected): void
    {
        $sut = new Composite(collect(), $attributes);
        $this->assertSame(0, $sut->getChildren()->count());

        $sut->setMode('stack')->setChildren($collection);
        $this->assertSame(5, $sut->getChildren()->count());

        $this->assertTrue($sut->execute());
        $this->assertSame(0, $sut->getChildren()->count());
        $this->assertInstanceOf(InterfaceShare::class, $sut->getCurrent());

        $shared = $sut->getCurrent()->share();
        $this->assertSame($expected, $shared->data ?? null);
    }

    public static function providerChildrenFail(): array
    {
        $map = fn (int $i, ?object $data = null) => \array_map(fn ($index) => self::generateChild($index !== 4, $data), range(1, $i));
        return [
            'children-no-data'      => [collect($map(5)), null, null],
            'children-init-data'    => [collect($map(5)), (object) ['data' => 42], 46],
            'children-inner-data'   => [collect($map(5, (object) ['data' => 666])), null, 670],
        ];
    }

    #[Group('failure')]
    #[Group('queue')]
    #[Group('execute')]
    #[DataProvider('providerChildrenFail')]
    public function test_failure_queue_mode(Collection $collection, ?object $attributes, ?int $expected): void
    {
        $sut = new Composite($collection, $attributes);
        $this->assertSame(5, $sut->getChildren()->count());

        $sut->setMode('queue');
        $this->assertFalse($sut->execute());
        $this->assertSame(1, $sut->getChildren()->count());

        $shared = $sut->getCurrent()->share();
        $this->assertSame($expected, $shared->data ?? null);
    }

    #[Group('failure')]
    #[Group('stack')]
    #[Group('execute')]
    #[DataProvider('providerChildrenFail')]
    public function test_failure_stack_mode(Collection $collection, ?object $attributes, ?int $expected): void
    {
        $sut = new Composite($collection, $attributes);
        $this->assertSame(5, $sut->getChildren()->count());

        $sut->setMode('stack');
        $this->assertFalse($sut->execute());
        $this->assertSame(3, $sut->getChildren()->count());

        $shared = $sut->getCurrent()->share();
        if ($expected === null) {
            $this->assertNull($shared);
        } else {
            $this->assertSame($expected - 2, $shared->data);
        }
    }

    #[Group('exception')]
    #[Group('mode')]
    public function test_exception_setMode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Available modes [queue|stack]");
        $sut = new Composite(collect());
        $sut->setMode('foobar');
    }

    #[Group('exception')]
    #[Group('mode')]
    #[Group('execute')]
    public function test_exception_mode_execute(): void
    {
        $this->expectException(UnhandledMatchError::class);
        $this->expectExceptionMessage("Available modes [queue|stack]");
        $sut = new Composite(collect());
        $property   = new \ReflectionProperty($sut, 'mode');
        $property->setAccessible(true);
        $property->setValue($sut, 'foobar');
        $sut->execute();
    }

    #[Group('success')]
    #[Group('shift')]
    #[Group('pop')]
    public function test_success_shift_pop(): void
    {
        $collection = self::providerChildrenFail()['children-inner-data'][0];
        $sut = new Composite($collection);
        $this->assertSame(5, $sut->getChildren()->count());

        $sut->shift();
        $shifted = $sut->getCurrent();
        $this->assertSame(4, $sut->getChildren()->count());

        $sut->pop();
        $poped = $sut->getCurrent();
        $this->assertSame(3, $sut->getChildren()->count());

        $this->assertNotSame($shifted->uuid, $poped->uuid);
    }
}
