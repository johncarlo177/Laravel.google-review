<?php

namespace App\Events;

use App\Models\LeadFormResponse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadFormResponseReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public LeadFormResponse $response;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(LeadFormResponse $response)
    {
        $this->response = $response;
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
