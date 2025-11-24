<?php

namespace App\Events;

use App\Models\QRCode;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * @deprecated
 */
class QRCodeLogoUploaded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $qrcode;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(QRCode $qrcode)
    {
        $this->qrcode = $qrcode;
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
