<?php

declare(strict_types=1);

namespace Tests\Unit\Customizations\Composites;

use App\Customizations\Components\interfaces\InterfaceComponent;
use App\Customizations\Composites\DownloadedFilesCreateComponent;
use App\Customizations\Composites\interfaces\InterfaceShare;
use App\Customizations\Traits\ShareTrait;
use App\Models\RemoteFeeds;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(DownloadedFilesCreateComponent::class)]
#[UsesClass(ShareTrait::class)]
class DownloadedFilesCreateComponentTest extends TestCase
{
    #[Group('success')]
    #[Group('constructor')]
    public function test_success_constructor(): DownloadedFilesCreateComponent
    {
        $sut = new DownloadedFilesCreateComponent();
        $this->assertInstanceOf(InterfaceComponent::class, $sut);
        $this->assertInstanceOf(InterfaceShare::class, $sut);
        return $sut;
    }

    public static function providerWithExistingFileAndModel(): array
    {
        return [
            'pornhub'   => [[
                'filename'  => "json_feed_pornstars.json",
                'disk'      => 'downloads',
                'mime_type' => 'application/json',
                'model'     => [[RemoteFeeds::class, 'find'], [1]]
            ],],
        ];
    }

    #[Group('success')]
    #[Group('execute')]
    #[Group('model')]
    #[DataProvider('providerWithExistingFileAndModel')]
    public function test_success_with_existing_file_and_model_execute(array $acquire): void
    {
        $sut = new DownloadedFilesCreateComponent();
        [$callback, $id] = $acquire['model'];
        $acquire['model'] = \call_user_func_array($callback, $id);

        $this->assertDatabaseEmpty('downloaded_files');
        $this->assertNull($acquire['model']->downloaded);

        $sut->acquire((object) $acquire);

        $this->assertTrue($sut->execute());

        $this->assertDatabaseCount('downloaded_files', 1);

        $shared = $sut->share();
        $this->assertSame(1, $shared->model->download_counter);
        $this->assertNotNull($shared->model->downloaded_file_id);
        $this->assertSame($acquire['filename'], $shared->model->downloaded->filename);
        $this->assertSame($acquire['disk'], $shared->model->downloaded->disk);
        $this->assertSame($acquire['mime_type'], $shared->model->downloaded->mime_type);
        $this->assertGreaterThan(0, $shared->model->downloaded->filesize);
        $this->assertFalse($shared->model->downloaded->is_cached);
    }

    public static function providerWithExistingFiles(): array
    {
        $previous = self::providerWithExistingFileAndModel();
        unset($previous['pornhub'][0]['model']);
        
        $provider = [
            'image 1' => [[
                'filename'  => "Screenshot1.png",
                'disk'      => 'thumbnails',
                'mime_type' => 'image/png',
            ],],
            'image 2' => [[
                'filename'  => "Screenshot2.png",
                'disk'      => 'thumbnails',
                'mime_type' => 'image/png',
            ],],
        ];

        return $provider + $previous;
    }

    #[Group('success')]
    #[Group('execute')]
    #[Depends('test_success_constructor')]
    #[DataProvider('providerWithExistingFiles')]
    public function test_success_with_existing_file(array $acquire, DownloadedFilesCreateComponent $sut): void
    {
        $this->assertDatabaseEmpty('downloaded_files');

        $sut->acquire((object) $acquire);
        $this->assertTrue($sut->execute());
        $this->assertNull($sut->share());

        $this->assertDatabaseCount('downloaded_files', 1);
        $this->assertDatabaseHas('downloaded_files', $acquire);
    }

    #[Group('exception')]
    #[Group('execute')]
    #[Depends('test_success_constructor')]
    public function test_exception_transaction(DownloadedFilesCreateComponent $sut): void
    {
        $this->assertDatabaseEmpty('downloaded_files');

        $sut->acquire((object) [
            'filename'  => ['invalid-data' => "Screenshot2.png"],
            'disk'      => 'thumbnails',
            'mime_type' => 'image/png',
        ]);
        $this->assertFalse($sut->execute());
        $this->assertDatabaseEmpty('downloaded_files');
    }

    public static function providerFake(): array
    {
        return [
            'fake 1'    => [[
                'filename'  => \implode('.', [fake()->word(), fake()->fileExtension()]),
                'disk'      => 'downloads',
                'mime_type' => fake()->mimeType(),
            ],],
            'fake 2'    => [[
                'filename'  => \implode('.', [fake()->word(), fake()->fileExtension()]),
                'disk'      => 'thumbnails',
                'mime_type' => fake()->mimeType(),
            ],],
            'fake 3'    => [[
                'filename'  => \implode('.', [fake()->word(), fake()->fileExtension()]),
                'disk'      => 'downloads',
                'mime_type' => fake()->mimeType(),
            ],],
        ];
    }

    #[Group('success')]
    #[Group('execute')]
    #[Depends('test_success_constructor')]
    #[DataProvider('providerFake')]
    public function test_success_without_existing_files(array $acquire, DownloadedFilesCreateComponent $sut): void
    {
        $this->assertDatabaseEmpty('downloaded_files');

        $sut->acquire((object) $acquire);
        $this->assertTrue($sut->execute());
        $this->assertNull($sut->share());

        $acquire['filesize'] = 0;

        $this->assertDatabaseCount('downloaded_files', 1);
        $this->assertDatabaseHas('downloaded_files', $acquire);
    }
}
