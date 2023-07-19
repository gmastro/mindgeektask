<?php
declare(strict_types=1);

namespace Tests\Feature\Listeners;

use App\Events\RemoteFeedCreated;
use App\Jobs\Common\CacheJob;
use App\Jobs\Common\DownloadJob;
use App\Listeners\RemoteFeedCreatedListener;
use App\Models\RemoteFeeds;
use Exception;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use Tests\TestCase;

#[CoversClass(RemoteFeedCreatedListener::class)]
#[UsesClass(RemoteFeedCreated::class)]
#[UsesClass(RemoteFeeds::class)]
class RemoteFeedCreatedListenerTest extends TestCase
{
    #[Group('failure')]
    #[Group('handle')]
    public function test_failure_handle()
    {
        Bus::fake();
        $sut = new RemoteFeedCreatedListener();

        RemoteFeeds::unsetEventDispatcher();
        $model = RemoteFeeds::factory()->count(1)->create([
            'source'    => "https://place-hold.it/244x344/666321/123666.jpg&text=lorem-ipsum&bold&italic&fontsize=11",
            'is_active' => true,
            'handle'    => [],
            'name'      => 'PlaceHoldJPG',
        ])->first();

        $event = new RemoteFeedCreated($model);
        $this->assertFalse($sut->handle($event));
        Bus::assertNothingDispatched();
    }

    #[Group('success')]
    #[Group('handle')]
    #[Group('bus')]
    public function test_success_handle()
    {
        $sut = new RemoteFeedCreatedListener();

        /**
         * @var FilesystemManager $storage
         */
        Storage::fake('moufa');
        Bus::fake();

        RemoteFeeds::unsetEventDispatcher();
        $model = RemoteFeeds::factory()->count(1)->create([
            'source'    => "https://place-hold.it/244x344/666321/123666.jpg&text=lorem-ipsum&bold&italic&fontsize=11",
            'is_active' => true,
            'handle'    => [DownloadJob::class => ['moufa']],
            'name'      => 'PlaceHoldJPG',
        ])->first();

        $event = new RemoteFeedCreated($model);
        $this->assertNull($sut->handle($event));
        Bus::assertDispatchedWithoutChain(DownloadJob::class);
    }

    /**
     * This is a failure of a test. Need some input to fix it, as there is nothing in documentation to explain how to
     * cover this case.
     * All sources found were from stack-overflow and all for other/different matters.
     * Displays a hint of what to expect.
     */
    #[Group('exception')]
    #[Group('handle')]
    #[Group('bus')]
    public function test_exception_handle()
    {
        Bus::fake();
        $sut = new RemoteFeedCreatedListener();
        
        RemoteFeeds::unsetEventDispatcher();
        $model = RemoteFeeds::factory()->count(1)->create([
            'source'    => "https://place-hold.it/244x344/666321/123666.jpg&text=lorem-ipsum&bold&italic&fontsize=11",
            'is_active' => true,
            'handle'    => [CacheJob::class => []],
            'name'      => 'PlaceHoldJPG',
        ])->first();

        $event = new RemoteFeedCreated($model);
        Log::shouldReceive('error')->with('Failed getting content for feed', [
            'id'        => $model->id,
            'source'    => $model->source,
            'message'   => 'whatever',
        ]);
        $this->assertNull($sut->failed($event, new Exception('whatever')));
        Bus::assertNothingDispatched();
    }
}
