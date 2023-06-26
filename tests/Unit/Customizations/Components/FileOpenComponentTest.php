<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Components;

use App\Customizations\Components\FileOpenComponent;
use App\Customizations\Traits\ErrorCodeTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(FileOpenComponent::class)]
#[UsesClass(ErrorCodeTrait::class)]
class FileOpenComponentTest extends TestCase
{
    public static function providerFilenames(): array
    {
        return [
            'write'         => ["moufa.txt", "w"],
            'write-binary'  => ["moufa.bin", "wb"],
        ];
    }

    #[Group('constructor')]
    #[Group('success')]
    #[Group('write')]
    #[DataProvider('providerFilenames')]
    public function test_success_constructor_mode_write(string $filename, string $mode): void
    {
        $directory = config("filesystems.disks.downloads.root");
        $filename  = \sprintf("%s/%s", $directory, $filename);

        $sut = new FileOpenComponent($filename, $mode);
        $resource = $sut->getHandle();
        $this->assertFalse($resource);
        $this->assertIsNotResource($resource);
        $this->assertFileDoesNotExist($filename);
        
        $sut->execute();
        $this->assertFalse($sut->hasErrors());
        $this->assertSame(0, $sut->getErrorCode());
        $this->assertSame([], $sut->getErrorBatch());
        
        $resource = $sut->getHandle();
        $this->assertIsResource($resource);

        $sut->close();
        $this->assertIsClosedResource($resource);

        $this->assertFileExists($filename);
        $this->assertStringEqualsFile($filename, "");

        unlink($filename);
    }

    #[Group('destructor')]
    #[Group('success')]
    #[Group('write')]
    #[DataProvider('providerFilenames')]
    public function test_success_destructor_mode_write(string $filename, string $mode): void
    {
        $directory = config("filesystems.disks.downloads.root");
        $filename  = \sprintf("%s/%s", $directory, $filename);

        $sut = new FileOpenComponent($filename, $mode);
        $sut->execute();
        $resource = $sut->getHandle();
        $this->assertIsResource($resource);

        unset($sut);
        $this->assertIsClosedResource($resource);

        $this->assertFileExists($filename);
        $this->assertStringEqualsFile($filename, "");

        unlink($filename);
    }

    #[Group('constructor')]
    #[Group('failure')]
    public function test_failure_constructor_filename_is_directory(): void
    {
        $filename = config("filesystems.disks.downloads.root");

        $sut = new FileOpenComponent($filename);
        $sut->execute();
        $this->assertTrue($sut->hasErrors());
        $this->assertGreaterThan(0, $sut->getErrorCode());
        $this->assertSame($sut::DIRECTORY_PATH, $sut->getErrorCode());
        $this->assertSame([
            $sut::DIRECTORY_PATH => null,
        ], $sut->getErrorBatch());
    }

    #[Group('constructor')]
    #[Group('failure')]
    public function test_failure_constructor_null_filename(): void
    {
        $sut = new FileOpenComponent(null);
        $sut->execute();
        $this->assertTrue($sut->hasErrors());
        $this->assertGreaterThan(0, $sut->getErrorCode());
        $this->assertSame($sut::FILE_PATH, $sut->getErrorCode());
        $this->assertSame([
            $sut::FILE_PATH => null,
        ], $sut->getErrorBatch());
    }

    #[Group('setters')]
    #[Group('success')]
    #[Group('write')]
    #[DataProvider('providerFilenames')]
    public function test_success_set_filename_write_mode(string $filename, string $mode): void
    {
        $directory = config("filesystems.disks.downloads.root");
        $filename  = \sprintf("%s/%s", $directory, $filename);

        $sut = new FileOpenComponent(null, $mode);
        $sut->setFilename($filename);
        $sut->execute();
        unset($sut);

        $this->assertFileExists($filename);
        $this->assertStringEqualsFile($filename, "");

        unlink($filename);
    }
}
