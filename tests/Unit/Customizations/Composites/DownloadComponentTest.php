<?php

declare(strict_types=1);

namespace Tests\Unit\Customizations\Composites;

use App\Customizations\Components\CurlComponent;
use App\Customizations\Components\interfaces\InterfaceComponent;
use App\Customizations\Composites\DownloadComponent;
use App\Customizations\Composites\ExamineComponent;
use App\Customizations\Composites\interfaces\InterfaceShare;
use App\Customizations\Traits\ShareTrait;
use DomainException;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(DownloadComponent::class)]
#[UsesClass(ExamineComponent::class)]
#[UsesClass(ShareTrait::class)]
class DownloadComponentTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('moufa');
    }

    #[Group('success')]
    #[Group('constructor')]
    public function test_success_constructor(): DownloadComponent
    {
        $sut = new DownloadComponent();
        $this->assertInstanceOf(InterfaceComponent::class, $sut);
        $this->assertInstanceOf(InterfaceShare::class, $sut);
        return $sut;
    }

    private static function setExaminer(array $acquire): object
    {
        $component = new ExamineComponent();
        $component->acquire((object) $acquire)->execute();
        return $component->share();
    }

    private static function coughtExaminer(array $acquire): object
    {
        $examine = new CurlComponent([
            CURLOPT_URL             => $acquire['source'],
            CURLOPT_NOBODY          => true,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_FILETIME        => true,
        ]);
        $examine->execute();

        $acquire['examine'] = $examine;
        return (object) $acquire;
    }

    public static function providerValidUrls(): array
    {
        return [
            'hamilton-image'=> [self::setExaminer([
                'disk'   => 'moufa',
                'source' => "https://www.hamiltonstaracademy.com/images/frontpage/portfolio/fullsize/Screenshot1.png"
            ])],
            'example.com'   => [self::setExaminer([
                'disk'   => 'moufa',
                'source' => "https://example.com"
            ])],
        ];
    }

    #[Group('success')]
    #[Group('execute')]
    #[DataProvider('providerValidUrls')]
    public function test_success_execute_without_model(object $acquire): void
    {
        $sut = new DownloadComponent();
        $sut->acquire($acquire);
        $this->assertTrue($sut->execute());

        $shared = $sut->share();
        $this->assertObjectHasProperty('disk', $shared);
        $this->assertObjectHasProperty('filename', $shared);
        $this->assertObjectHasProperty('fullpath', $shared);
        $this->assertObjectHasProperty('mime_type', $shared);

        $this->assertObjectNotHasProperty('examine', $shared);
        $this->assertObjectNotHasProperty('source', $shared);
        $this->assertObjectNotHasProperty('model', $shared);

        $this->assertFileExists($shared->fullpath);
    }

    #[Group('exception')]
    #[Group('execute')]
    public function test_exception_invalid_disk(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Disk [epic-fail] does not have a configured driver.");
        $sut = new DownloadComponent();
        $sut->acquire((object) ['disk' => 'epic-fail'])->execute();
    }

    public static function providerFromRedirects(): array
    {
        return [
            'google-redirect'   => [self::coughtExaminer([
                'disk'   => 'moufa',
                'source' => "https://google.com"
            ])],
        ];
    }

    #[Group('failure')]
    #[Group('execute')]
    #[DataProvider('providerFromRedirects')]
    public function test_failure_no_content(object $acquire): void
    {
        // $this->expectException(DomainException::class);
        // $this->expectExceptionMessageMatches("%^Terminated with the following errors: .*%");
        $sut = new DownloadComponent();
        $this->assertFalse($sut->acquire($acquire)->execute());
    }
}
