<?php
declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Events\RemoteFeedCreated;
use App\Events\RemoteFeedDeleting;
use App\Jobs\Common\DownloadJob;
use App\Listeners\RemoteFeedCreatedListener;
use App\Listeners\RemoteFeedDeletingListener;
use App\Models\DownloadedFiles;
use App\Models\RemoteFeeds;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(RemoteFeeds::class)]
#[UsesClass(RemoteFeedCreated::class)]
#[UsesClass(RemoteFeedCreatedListener::class)]
class RemoteFeedsTest extends TestCase
{
    #[Group('success')]
    #[Group('seed')]
    public function test_success_seed()
    {
        $this->assertDatabaseCount('remote_feeds', 1);
    }

    #[Group('success')]
    #[Group('seed')]
    #[Group('relations')]
    public function test_success_seed_relations()
    {
        $sut = RemoteFeeds::find(1);
        $this->assertSame(0, $sut->pornstars->count());
        $this->assertSame(0, $sut->thumbnails->count());
        $this->assertSame(0, $sut->downloaded_files->count());
        $this->assertNull($sut->downloaded);
    }

    #[Group('success')]
    #[Group('factory')]
    #[Group('without-events')]
    public function test_success_factory_without_events()
    {
        RemoteFeeds::unsetEventDispatcher();
        RemoteFeeds::factory()->count(5)->create();
        $this->assertDatabaseCount('remote_feeds', 6);
    }

    #[Group('factory')]
    #[Group('success')]
    #[Group('without-events')]
    #[Group('chain')]
    public function test_success_chain_without_events()
    {
        RemoteFeeds::unsetEventDispatcher();
        $collection = RemoteFeeds::factory()->count(1)->create([
            'source'    => "https://place-hold.it/244x344/666321/123666.jpg&text=lorem-ipsum&bold&italic&fontsize=11",
            'is_active' => true,
            'handle'    => [DownloadJob::class => ['moufa']],
            'name'      => 'PlaceHoldJPG',
        ]);
        

        $sut = $collection->first();
        $this->assertInstanceOf(Collection::class, $sut->chain);
        $this->assertTrue($sut->chain->isNotEmpty());
        $this->assertInstanceOf(DownloadJob::class, $sut->chain->first());
    }

    #[Group('factory')]
    #[Group('failure')]
    #[Group('without-events')]
    #[Group('chain')]
    public function test_failure_chain_without_events()
    {
        RemoteFeeds::unsetEventDispatcher();
        $collection = RemoteFeeds::factory()->count(1)->create([
            'source'    => "https://place-hold.it/244x344/666321/123666.jpg&text=lorem-ipsum&bold&italic&fontsize=11",
            'is_active' => true,
            'handle'    => ['notAClass' => [], 'thisIsNotAnotherClass' => [123]],
            'name'      => 'PlaceHoldJPG',
        ]);
        
        $sut = $collection->first();
        $this->assertInstanceOf(Collection::class, $sut->chain);
        $this->assertTrue($sut->chain->isEmpty());
    }

    #[Group('factory')]
    #[Group('failure')]
    #[Group('observer')]
    #[Group('created')]
    public function test_failure_observer_created()
    {
        /**
         * @var FilesystemManager $storage
         */
        Storage::fake('moufa');
        Event::fake([
            RemoteFeedCreated::class
        ]);

        Event::assertListening(RemoteFeedCreated::class, RemoteFeedCreatedListener::class);

        RemoteFeeds::factory()->count(1)->create([
            'source'    => "https://place-hold.it/244x344/666321/123666.png&text=lorem-ipsum&bold&italic&fontsize=11",
            'is_active' => false,
            'handle'    => [DownloadJob::class => ['moufa']],
            'name'      => 'PlaceHoldPNG',
        ]);

        Event::assertNotDispatched(RemoteFeedCreated::class);
        $this->assertDatabaseHas(app(RemoteFeeds::class)->getTable(), ['name' => 'PlaceHoldPNG']);
    }

    #[Group('factory')]
    #[Group('success')]
    #[Group('observer')]
    #[Group('created')]
    public function test_success_observer_created()
    {
        /**
         * @var FilesystemManager $storage
         */
        Storage::fake('moufa');
        Event::fake([
            RemoteFeedCreated::class
        ]);

        Event::assertListening(RemoteFeedCreated::class, RemoteFeedCreatedListener::class);

        RemoteFeeds::factory()->count(1)->create([
            'source'    => "https://place-hold.it/244x344/666321/123666.jpg&text=lorem-ipsum&bold&italic&fontsize=11",
            'is_active' => true,
            'handle'    => [DownloadJob::class => ['moufa']],
            'name'      => 'PlaceHoldJPG',
        ]);

        Event::assertDispatched(RemoteFeedCreated::class);
        $this->assertDatabaseHas(app(RemoteFeeds::class)->getTable(), ['name' => 'PlaceHoldJPG']);
    }

    #[Group('factory')]
    #[Group('success')]
    #[Group('observer')]
    #[Group('deleting')]
    public function test_success_observer_deleting()
    {
        /**
         * @var FilesystemManager $storage
         */
        $storage = Storage::fake('moufa');
        Event::fake([
            RemoteFeedDeleting::class
        ]);

        Event::assertListening(RemoteFeedDeleting::class, RemoteFeedDeletingListener::class);

        $sut = RemoteFeeds::factory()->count(1)->create([
            'source'    => "https://place-hold.it/244x344/666321/123666.jpg&text=lorem-ipsum&bold&italic&fontsize=11",
            'is_active' => true,
            'handle'    => [DownloadJob::class => ['moufa']],
            'name'      => 'PlaceHoldJPG',
        ])->first();

        $this->assertNotNull($sut->downloaded_file_id);
        $sut->delete();
        Event::assertDispatched(RemoteFeedDeleting::class);
        $this->assertDatabaseMissing(app(RemoteFeeds::class)->getTable(), ['name' => 'PlaceHoldJPG']);
    }
}
