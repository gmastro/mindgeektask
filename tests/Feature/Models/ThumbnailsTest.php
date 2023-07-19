<?php
declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Pornstars;
use App\Models\PornstarsThumbnails;
use App\Models\RemoteFeeds;
use App\Models\Thumbnails;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[CoversClass(Thumbnails::class)]
class ThumbnailsTest extends TestCase
{
    private $feed;
    private $pornstars;

    public function setUp(): void
    {
        parent::setUp();
        RemoteFeeds::unsetEventDispatcher();

        $this->feed       = RemoteFeeds::all()->first();
        $this->pornstars  = Pornstars::factory()->count(2)->create([
            'remote_feed_id'    => $this->feed->id,
        ]);
    }

    #[Group('factory')]
    public function test_success_factory()
    {
        $this->assertDatabaseCount('thumbnails', 0);
        Thumbnails::factory()->count(5)->create();
        $this->assertDatabaseCount('thumbnails', 5);
    }

    #[Group('factory')]
    #[Group('relations')]
    public function test_success_factory_relations()
    {
        $this->assertDatabaseCount('thumbnails', 0);
        $collection = Thumbnails::factory()->count(5)->create([
            'remote_feed_id'    => $this->feed->id,
        ]);
        $this->assertDatabaseCount('thumbnails', 5);

        $keys = $this->pornstars->modelKeys();
        $collection->map(function ($model, $i) use ($keys) {
            $key = $keys[\intval($i%2 === 0)];
            $pornstar = $this->pornstars->find($key);
            $model->pornstars()->sync($pornstar);
            $model->save();
        });

        $this->assertSame(2, PornstarsThumbnails::where(['pornstar_id' => $keys[0]])->get()->count());
        $this->assertSame(3, PornstarsThumbnails::where(['pornstar_id' => $keys[1]])->get()->count());

        $sut = Thumbnails::first();

        $this->assertModelExists($sut->remoteFeeds);
        $this->assertModelExists($sut->downloaded);
        $this->assertModelExists($sut->pornstars->first());
    }
}
