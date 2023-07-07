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

#[CoversClass(Pornstars::class)]
class PornstarsTest extends TestCase
{
    private $feed;
    private $thumbnails;

    public function setUp(): void
    {
        parent::setUp();
        RemoteFeeds::unsetEventDispatcher();
        $this->feed       = RemoteFeeds::all()->first();
        $this->thumbnails = Thumbnails::factory()->count(2)->create([
            'remote_feed_id'    => $this->feed->id,
        ]);
    }

    #[Group('factory')]
    public function test_success_factory()
    {
        $this->assertDatabaseCount('pornstars', 0);
        Pornstars::factory()->count(5)->create();
        $this->assertDatabaseCount('pornstars', 5);
    }

    #[Group('factory')]
    #[Group('relations')]
    public function test_success_factory_relations()
    {
        $this->assertDatabaseCount('pornstars', 0);
        \array_map(fn ($id) => Pornstars::factory()->create([
            'id'                => $id,
            'remote_feed_id'    => $this->feed->id,
        ]), range(1, 5));
        $this->assertDatabaseCount('pornstars', 5);

        $keys = Thumbnails::all()->modelKeys();
        Pornstars::all()->map(function ($model) use ($keys) {
            $key = $keys[\intval($model->id%2 === 0)];
            $thumbnail = $this->thumbnails->find($key);
            $model->thumbnails()->sync($thumbnail);
            $model->save();
        });

        $this->assertSame(3, PornstarsThumbnails::where(['thumbnail_id' => $keys[0]])->get()->count());
        $this->assertSame(2, PornstarsThumbnails::where(['thumbnail_id' => $keys[1]])->get()->count());

        $sut = Pornstars::first();

        $this->assertModelExists($sut->remoteFeeds);
        $this->assertModelExists($sut->thumbnails->first());
        $this->assertCount(1, $sut->downloads);
    }
}
