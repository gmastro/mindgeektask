<?php

namespace App\Events;

use App\Models\RemoteFeeds;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemoteFeedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $composite;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public RemoteFeeds $feed,
        public bool $isExamined = false,
        public bool $isDownloaded = false,
        public bool $isUpdated = false,
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
