<?php
declare(strict_types=1);

namespace Tests\Unit\Customizations\Adapters;

use App\Customizations\Adapters\RedisFileCachingAdapter;
use App\Customizations\Composites\Composite;
use App\Customizations\Composites\DownloadComponent;
use App\Customizations\Composites\DownloadedFilesCreateComponent;
use App\Customizations\Composites\ExamineComponent;
use App\Models\DownloadedFiles;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(RedisFileCachingAdapter::class)]
#[UsesClass(Composite::class)]
#[UsesClass(ExamineComponent::class)]
#[UsesClass(DownloadComponent::class)]
#[UsesClass(DownloadedFilesCreateComponent::class)]
#[UsesClass(DownloadedFiles::class)]
class RedisFileCachingAdapterTest extends TestCase
{
    public static function providerUrls(): array
    {
        return [
            'images-only'   => [
                [
                    "https://fakeimg.pl/300/",
                    "https://fakeimg.pl/250x100/",
                    "https://fakeimg.pl/350x200/?text=Hello",
                ],
                3
            ],
            'mixed'         => [
                [
                    "https://fakeimg.pl/350x200/?text=World&font=lobster",
                    "https://microsoftedge.github.io/Demos/json-dummy-data/64KB.json",
                ],
                1
            ],
            "no-images"     => [
                [
                    "https://example.com"
                ],
                0
            ],
        ];
    }

    private static function download(string $url): Composite
    {
        $composite = new Composite(collect([
            new ExamineComponent(),
            new DownloadComponent(),
            new DownloadedFilesCreateComponent(),
        ]), (object) [
            'source'    => $url,
            'disk'      => 'moufa',
        ]);
        $composite->execute();
        return $composite;
    }

    #[Group('constructor')]
    #[Group('success')]
    #[DataProvider('providerUrls')]
    public function test_success_constructor(array $url, int $expected): void
    {
        /**
         * @var FilesystemManager $storage
         */
        $storage = Storage::fake('moufa');

        \array_map(fn ($link) => self::download($link), $url);
        $collection = DownloadedFiles::all();

        Redis::pipeline(fn($pipe) => (new RedisFileCachingAdapter($pipe, $collection))->execute());
        
        $collection->map(function ($model) use ($storage) {
            $this->assertSame($model->is_cached, Redis::command("exists", ["key:" . $model->md5_hash]));
            if ($model->is_cached) {
                // $this->assertFileEquals($storage->get($model->filename), Redis::get("key:" . $model->md5_hash));
                $info = (new \finfo(FILEINFO_MIME))->buffer(Redis::get("key:" . $model->md5_hash));
                $this->assertStringStartsWith($model->mime_type, $info);
            }
        });

        $this->assertSame($expected, $collection->filter(fn($model) => $model->is_cached)->count());
    }

    #[Group('constructor')]
    #[Group('failure')]
    #[Group('missing-file')]
    public function test_failure_missing_file()
    {
        /**
         * @var FilesystemManager $storage
         */
        $storage = Storage::fake('moufa');
        \array_map(fn ($link) => self::download($link), self::providerUrls()['images-only'][0]);
        $collection = DownloadedFiles::all();
        $collection->map(fn ($model) => $storage->delete($model->filename));

        Redis::pipeline(fn($pipe) => (new RedisFileCachingAdapter($pipe, $collection))->execute());

        $collection->map(function ($model) {
            $this->assertNotSame($model->is_cached, Redis::command("exists", ["key:" . $model->md5_hash]));
        });
    }

    #[Group('constructor')]
    #[Group('failure')]
    #[Group('soft-delete')]
    public function test_failure_soft_deleted()
    {
        /**
         * @var FilesystemManager $storage
         */
        $storage = Storage::fake('moufa');
        \array_map(fn ($link) => self::download($link), self::providerUrls()['images-only'][0]);
        $collection = DownloadedFiles::all();
        $collection->map(fn ($model) => $model->delete());

        Redis::pipeline(fn($pipe) => (new RedisFileCachingAdapter($pipe, $collection))->execute());

        $collection->map(function ($model) {
            $this->assertFalse((bool) Redis::command("exists", ["key:" . $model->md5_hash]));
        });
    }

    #[Group('constructor')]
    #[Group('success')]
    #[Group('unlink')]
    public function test_success_unlink()
    {
        /**
         * @var FilesystemManager $storage
         */
        $storage = Storage::fake('moufa');
        \array_map(fn ($link) => self::download($link), self::providerUrls()['images-only'][0]);
        $collection = DownloadedFiles::all();

        Redis::pipeline(fn($pipe) => (new RedisFileCachingAdapter($pipe, $collection))->execute());

        $collection->map(function ($model) {
            $model->is_cached = false;
            $model->save();
            $this->assertTrue((bool) Redis::command("exists", ["key:" . $model->md5_hash]));
        });

        Redis::pipeline(fn($pipe) => (new RedisFileCachingAdapter($pipe, $collection))->execute());

        $collection->map(function ($model) {
            $this->assertFalse((bool) Redis::command("exists", ["key:" . $model->md5_hash]));
        });
    }
}
