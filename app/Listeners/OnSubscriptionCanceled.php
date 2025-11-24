<?php

namespace App\Listeners;

use App\Events\SubscriptionCanceled;
use App\Models\Subscription;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OnSubscriptionCanceled extends DynamicListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
}
