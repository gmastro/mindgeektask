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
    #[Group('factory')]
    #[Group('without-events')]
    public function test_success_factory()
    {
        RemoteFeeds::unsetEventDispatcher();
        $this->assertDatabaseCount('remote_feeds', 1);
        RemoteFeeds::factory()->count(5)->create();
        $this->assertDatabaseCount('remote_feeds', 6);
    }
}
