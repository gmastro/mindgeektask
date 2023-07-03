<?php

declare(strict_types=1);

namespace Tests\Unit\Customizations\Composites;

use App\Customizations\Components\interfaces\InterfaceComponent;
use App\Customizations\Composites\ExamineComponent;
use App\Customizations\Composites\interfaces\InterfaceShare;
use App\Customizations\Traits\ShareTrait;
use App\Models\RemoteFeeds;
use App\Models\Thumbnails;
use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(ExamineComponent::class)]
#[UsesClass(ShareTrait::class)]
class ExamineComponentTest extends TestCase
{
    #[Group('success')]
    #[Group('constructor')]
    public function test_success_constructor(): ExamineComponent
    {
        $sut = new ExamineComponent();
        $this->assertInstanceOf(InterfaceComponent::class, $sut);
        $this->assertInstanceOf(InterfaceShare::class, $sut);
        return $sut;
    }

    public static function providerSuccessWithoutModel(): array
    {
        return [
            'hamilton-image'=> [(object) ['source' => "https://www.hamiltonstaracademy.com/images/frontpage/portfolio/fullsize/Screenshot1.png"]],
            'example.com'   => [(object) ['source' => "https://example.com"]],
        ];
    }

    #[Group('success')]
    #[Group('execute')]
    #[Depends('test_success_constructor')]
    #[DataProvider('providerSuccessWithoutModel')]
    public function test_success_execute_without_model(object $acquire, ExamineComponent $sut): void
    {
        $sut->acquire($acquire);
        $this->assertTrue($sut->execute());

        $shared = $sut->share();
        $this->assertObjectHasProperty('examine', $shared);

        $this->assertObjectNotHasProperty('source', $shared);
        $this->assertObjectNotHasProperty('disk', $shared);
        $this->assertObjectNotHasProperty('model', $shared);
    }

    public static function providerExceptionInvalidUrl(): array
    {
        return [
            'fake 1'    => [(object) ['source' => 'fooBar']],
            'fake 2'    => [(object) ['source' => 'file://fooBar']],
            'fake 3'    => [(object) ['source' => 'http://sub.i-am-not-supposed-to-be-any-domain-used.net/clients']],
            'fake 4'    => [(object) ['source' => 'https://sub.i-am-not-supposed-to-be-any-domain-used.net/clients?whatever=true']],
        ];
    }

    #[Group('exception')]
    #[Group('execute')]
    #[Depends('test_success_constructor')]
    #[DataProvider('providerExceptionInvalidUrl')]
    public function test_exception_invalid_url(object $acquire, ExamineComponent $sut): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches("%^Terminated with the following errors: .*%");
        $sut->acquire($acquire);
        $sut->execute();
    }

    public static function providerExceptionNotStatusCode200(): array
    {
        return [
            'hamilton'      => [(object) ['source' => "https://www.hamiltonstaracademy.com/"]],
            'google.com'    => [(object) ['source' => "https://google.com"]],
        ];
    }

    #[Group('exception')]
    #[Group('execute')]
    #[Depends('test_success_constructor')]
    #[DataProvider('providerExceptionNotStatusCode200')]
    public function test_exception_redirect(object $acquire, ExamineComponent $sut): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches("%^Terminated, expected status code 200, instead got \d+$%");
        $sut->acquire($acquire);
        $sut->execute();
    }

    public static function providerSuccessWithModel(): array
    {
        $past = now()->subYear(10);
        return [
            'image-1'       => [[
                'source'        => "https://www.hamiltonstaracademy.com/images/frontpage/portfolio/fullsize/Screenshot1.png",
                'disk'          => 'thumbnails',
                'model'         => [
                    Thumbnails::class, [
                        'created_at'    => $past,
                        'updated_at'    => $past,
                    ]
                ]
            ]],
            'image-2'       => [[
                'disk'          => 'thumbnails',
                'source'        => "https://www.hamiltonstaracademy.com/images/frontpage/portfolio/fullsize/Screenshot2.png",
                'model'         => [
                    Thumbnails::class, [
                        'created_at'    => $past,
                        'updated_at'    => null,
                    ]
                ]
            ]],
            'example'       => [[
                'source'        => "https://example.com",
                'disk'          => 'downloads',
                'model'         => [
                    RemoteFeeds::class, [
                        'source'            => "https://example.com",
                        'examine_counter'   => 42,
                        'created_at'        => $past,
                        'updated_at'        => $past,
                    ]
                ]
            ]],
        ];
    }

    #[Group('success')]
    #[Group('execute')]
    #[Group('model')]
    #[Depends('test_success_constructor')]
    #[DataProvider('providerSuccessWithModel')]
    public function test_success_execute_with_model(array $acquire, ExamineComponent $sut): void
    {
        [$modelClass, $modelData] = $acquire['model'];
        $modelClass::unsetEventDispatcher();
        $acquire['model'] = $modelClass::factory(1)->create($modelData)->first();
        $sut->acquire((object) $acquire);

        $this->assertTrue($sut->execute());

        $shared = $sut->share();
        $this->assertObjectHasProperty('examine', $shared);
        $this->assertObjectHasProperty('disk', $shared);
        $this->assertObjectHasProperty('model', $shared);

        $this->assertObjectNotHasProperty('source', $shared);

        if($modelClass !== RemoteFeeds::class) {
            return;
        }

        $this->assertSame(43, $shared->model->examine_counter);
    }

    #[Group('exception')]
    #[Group('execute')]
    #[Group('model')]
    #[Depends('test_success_constructor')]
    #[DataProvider('providerSuccessWithModel')]
    public function test_exception_model_up_to_date(array $acquire, ExamineComponent $sut): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches("%^Terminated, local content is up-to-date.*%");

        [$modelClass, $modelData] = $acquire['model'];
        $modelData['updated_at'] = now();
        $modelClass::unsetEventDispatcher();
        $acquire['model'] = $modelClass::factory(1)->create($modelData)->first();
        $sut->acquire((object) $acquire);

        $sut->execute();
    }
}
