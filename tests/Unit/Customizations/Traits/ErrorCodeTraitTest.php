<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Traits;

use App\Customizations\Traits\ErrorCodeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

#[CoversClass(ErrorCodeTrait::class)]
class ErrorCodeTraitTest extends TestCase
{
    public static function providerCodes()
    {
        $errorCodes = [];
        for($i = 1; $i < 100; $errorCodes["random-value-$i"] = [$i], ++$i);

        return fake()->randomElements($errorCodes, 10);
    }

    #[Group('success')]
    #[Group('setter')]
    #[Group('condition-true')]
    #[DataProvider('providerCodes')]
    public function test_success_seed_errors_true_condition(int $errorCode)
    {
        $sut = new class{
            use ErrorCodeTrait {setErrorCode as public;}
        };
        $output = $sut->setErrorCode($errorCode);
        $this->assertTrue($output);
        $this->assertSame($errorCode, $sut->getErrorCode());
        $this->assertSame([
            $errorCode => null
        ], $sut->getErrorBatch());
    }

    #[Group('success')]
    #[Group('setter')]
    #[Group('condition-false')]
    #[DataProvider('providerCodes')]
    public function test_success_seed_errors_false_condition(int $errorCode)
    {
        $sut = new class{
            use ErrorCodeTrait {setErrorCode as public;}
        };
        $output = $sut->setErrorCode($errorCode, false);
        $this->assertFalse($output);
        $this->assertSame(0, $sut->getErrorCode());
        $this->assertNotSame($errorCode, $sut->getErrorCode());
        $this->assertSame([], $sut->getErrorBatch());
    }

    #[Group('success')]
    #[Group('batch')]
    public function test_success_seed_zero_will_not_be_added_into_batch()
    {
        $sut = new class{
            use ErrorCodeTrait {setErrorCode as public;}
        };
        $output = $sut->setErrorCode(0, false);
        $this->assertFalse($output);
        $this->assertSame(0, $sut->getErrorCode());
        $this->assertSame([], $sut->getErrorBatch());

        $output = $sut->setErrorCode(42);
        $this->assertTrue($output);
        $this->assertSame(42, $sut->getErrorCode());
        $this->assertSame([
            42 => null
        ], $sut->getErrorBatch());

        $output = $sut->setErrorCode(0);
        $this->assertTrue($output);
        $this->assertSame(0, $sut->getErrorCode());
        $this->assertSame([
            42 => null
        ], $sut->getErrorBatch());
    }
}