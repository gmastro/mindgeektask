<?php
declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\DownloadedFiles;
use App\Models\RemoteFeeds;
use App\Models\Thumbnails;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\TestCase;

#[CoversClass(DownloadedFiles::class)]
class DownloadedFilesTest extends TestCase
{
    #[Group('factory')]
    public function test_success_factory()
    {
        $this->assertDatabaseCount('downloaded_files', 0);
        $collection = DownloadedFiles::factory()->count(5)->create();
        $this->assertDatabaseCount('downloaded_files', 5);

        $sut = $collection->first();
        RemoteFeeds::unsetEventDispatcher();
        RemoteFeeds::factory(10)->create([
            'downloaded_file_id'    => $sut->id
        ]);

        Thumbnails::unsetEventDispatcher();
        Thumbnails::factory(5)->create([
            'downloaded_file_id'    => $sut->id
        ]);

        $this->assertSame(10, $sut->remote_feeds->count());
        $this->assertSame(5, $sut->thumbnails->count());
    }

    public static function providerWithoutFiles(): array
    {
        return [
            'moufa'     => ['moufa', 'Screenshot1.png', 'image/png', true],
            'whatever'  => ['foo', 'Screenshot2.png', 'image/jpg', true],
        ];
    }

    #[Group('success')]
    #[Group('create')]
    #[DataProvider('providerWithoutFiles')]
    public function test_success_create_no_file(string $disk, string $filename, string $mime, bool $isCached)
    {
        /**
         * @var FilesystemManager $storage
         */
        $storage = Storage::fake($disk);
        $this->assertDatabaseCount('downloaded_files', 0);
        $sut = DownloadedFiles::create([
            'filename'  => $filename,
            'disk'      => $disk,
            'mime_type' => $mime,
            'is_cached' => $isCached,
        ]);

        $this->assertDatabaseCount('downloaded_files', 1);
        $this->assertSame(\md5($storage->path($filename)), $sut->md5_hash);
        $this->assertFalse($sut->is_cached);
        $this->assertSame(0, $sut->filesize);
    }

    public static function providerWithSampleFiles(): array
    {
        return [
            'screenshot2.png'   => ['thumbnails', 'Screenshot2.png', 'image/png', true],
            'sample.json'       => ['downloads',  'sample.json', 'application/json', false],
        ];
    }

    #[Group('success')]
    #[Group('create')]
    #[DataProvider('providerWithSampleFiles')]
    public function test_success_create_with_copied_files(string $disk, string $filename, string $mime, bool $isCached)
    {
        /**
         * @var FilesystemManager $from
         */
        $from = Storage::disk($disk);

        /**
         * @var FilesystemManager $storage
         */
        $storage = Storage::fake('moufa');
        $storage->put($filename, $from->get($filename));

        $this->assertDatabaseCount('downloaded_files', 0);
        $sut = DownloadedFiles::create([
            'filename'  => $filename,
            'disk'      => 'moufa',
            'mime_type' => $mime,
            'is_cached' => $isCached,
        ]);

        $this->assertDatabaseCount('downloaded_files', 1);
        $this->assertSame(\md5($storage->path($filename)), $sut->md5_hash);
        $this->assertSame($isCached, $sut->is_cached);
        $this->assertSame($storage->size($filename), $sut->filesize);
    }
}
