<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Traits;

use App\Customizations\Traits\ShareTrait;
use App\Models\RemoteFeeds;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use Tests\TestCase;

#[CoversClass(ShareTrait::class)]
class ShareTraitTest extends TestCase
{
    public static function providerAcquire(): array
    {
        return [
            'array' => [(object) ['array' => [fake()->words(), null, true, false]], ['array']],
            'int'   => [(object) ['int' => fake()->randomNumber(), 'bool' => fake()->boolean()], ['int']],
            'mixed' => [(object) ['int' => fake()->randomNumber(), 'string' => fake()->word()], ['int', 'string']],
        ];
    }

    #[Group('success')]
    #[Group('acquire')]
    #[DataProvider('providerAcquire')]
    public function test_success_acquire_content(object $acquired, array $expected): void
    {
        $sut = new class{
            use ShareTrait {
                has as public;
            }
        };
        $sut->acquire($acquired);
        foreach($expected as $key) {
            $this->assertTrue($sut->has($key));
        }
        $this->assertFalse($sut->has('no_such_key_is_provided111222333444'));
        $this->assertNull($sut->share());
    }

    public static function providerSet(): array
    {
        return [
            'model'     => ['key', 1],
            'string'    => ['key', fake()->url()],
            'int'       => ['int-key', fake()->randomNumber()],
            'double'    => ['double-key', fake()->randomFloat()],
            'empty'     => ['empty', null],
            'datetime'  => ['dt', new DateTimeImmutable('2019-09-01 15:00:00')]
        ];
    }

    #[Group('success')]
    #[Group('set')]
    #[DataProvider('providerSet')]
    public function test_success_set_content(string $key, mixed $value)
    {
        $sut = new class{
            use ShareTrait {
                has as public;
                set as public;
                unset as public;
            }
        };

        $this->assertNull($sut->share());

        $sut->set($key, $value);
        $this->assertFalse($sut->has($key));
        $this->assertIsObject($sut->share());
        $this->assertSame($value, $sut->share()->$key);
        $this->assertEquals((object) [$key => $value], $sut->share());
    }

    #[Group('success')]
    #[Group('append')]
    public function test_success_append_content()
    {
        $sut = new class{
            use ShareTrait {
                append as public;
                transfer as public;
                has as public;
                set as public;
                unset as public;
            }
        };

        $append = self::providerSet();

        $sut->append($append);
        $this->assertIsObject($sut->share());
        $this->assertEquals((object) $append, $sut->share());
        return $sut;
    }

    #[Group('success')]
    #[Group('unset')]
    #[Depends('test_success_append_content')]
    public function test_success_unset_content($sut): void
    {
        $this->assertObjectHasProperty('double', $sut->share());
        $sut->unset('double');
        $this->assertObjectNotHasProperty('double', $sut->share());
        $this->assertIsObject($sut->share());
    }

    #[Group('success')]
    #[Group('transfer')]
    #[Depends('test_success_append_content')]
    public function test_success_transfer_content($sut): void
    {
        $this->assertFalse($sut->has('model'));
        $sut->acquire((object)['model' => RemoteFeeds::find(1)]);
        $this->assertTrue($sut->has('model'));
        
        $this->assertIsArray($sut->share()->model);
        $this->assertObjectHasProperty('model', $sut->share());
        $sut->transfer('model');
        $this->assertInstanceOf(RemoteFeeds::class, $sut->share()->model);

        $this->assertObjectNotHasProperty('remoteFeedsModel', $sut->share());
        $sut->transfer('model', 'remoteFeedsModel');
        $this->assertInstanceOf(RemoteFeeds::class, $sut->share()->remoteFeedsModel);
        $this->assertEquals($sut->share()->model, $sut->share()->remoteFeedsModel);
    }
}
