<?php
declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\RemoteFeeds;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[CoversClass(RemoteFeeds::class)]
class RemoteFeedsTest extends TestCase
{
    #[Group('seed')]
    public function test_success_seed()
    {
        $this->assertDatabaseCount('remote_feeds', 1);
    }

    #[Group('factory')]
    #[Group('without-events')]
    public function test_success_factory()
    {
        RemoteFeeds::unsetEventDispatcher();
        RemoteFeeds::factory()->count(5)->create();
        $this->assertDatabaseCount('remote_feeds', 6);
    }

    #[Group('relations')]
    public function test_success_seed_relations()
    {
        $sut = RemoteFeeds::find(1);
        $this->assertSame(0, $sut->pornstars->count());
        $this->assertSame(0, $sut->thumbnails->count());
        $this->assertSame(0, $sut->downloaded_files->count());
        $this->assertSame(null, $sut->downloaded);
    }

    #[Group('factory')]
    #[Group('relations')]
    #[Group('without-events')]
    public function test_success_factory_relations()
    {
        // RemoteFeeds::unsetEventDispatcher();
        $sut = RemoteFeeds::all()->except([1]);
        $this->assertSame(0, $sut->count());

        // add some new factory content here
    }
}
