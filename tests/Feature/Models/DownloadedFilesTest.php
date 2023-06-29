<?php
declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\DownloadedFiles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[CoversClass(DownloadedFiles::class)]
class DownloadedFilesTest extends TestCase
{
    #[Group('factory')]
    public function test_success_factory()
    {
        $this->assertDatabaseCount('downloaded_files', 0);
        DownloadedFiles::factory()->count(5)->create();
        $this->assertDatabaseCount('downloaded_files', 5);
    }

    #[Group('create')]
    public function test_success_create()
    {
        $this->assertDatabaseCount('downloaded_files', 0);
        DownloadedFiles::create([
            'filename'  => "Screenshot1.png",
            'disk'      => "thumbnails",
            'mime_type' => 'images/png',
            'is_cached' => true,
        ]);
        $this->assertDatabaseCount('downloaded_files', 1);

        $model = DownloadedFiles::where(['filename' => "Screenshot1.png", 'disk' => 'thumbnails'])->get();
        $this->assertSame(
            \md5(\implode('/', [config("filesystems.disks.thumbnails.root"), "Screenshot1.png"])),
            $model->md5_hash
        );
        $this->assertGreaterThan(0, $model->filesize);
    }
}
