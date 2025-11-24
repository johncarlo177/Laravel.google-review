<?php

namespace App\Events;

use App\Models\Translation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TranslationActivated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Translation $translation;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Translation $translation)
    {
        $this->translation = $translation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
