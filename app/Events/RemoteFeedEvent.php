<?php

namespace App\Events;

use App\Customizations\Adapters\CurlDownloadAdapter;
use App\Customizations\Components\CurlComponent;
use App\Events\interfaces\InterfaceEvent;
use App\Models\RemoteFeeds;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemoteFeedEvent implements InterfaceEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public RemoteFeeds $feed,
        public string $disk,
        public CurlComponent $curl,
        public CurlDownloadAdapter $download
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
