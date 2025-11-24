<?php

namespace App\Listeners;

use App\Events\SubscriptionVerified;
use App\Models\Subscription;
use App\Models\SubscriptionStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class AssignActiveSubscriptionStatus
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

    /**
     * Handle the event.
     *
     * @param  \App\Events\SubscriptionVerified  $event
     * @return void
     */
    public function handle(SubscriptionVerified $event)
    {
        $status = new SubscriptionStatus([
            'subscription_id' => $event->subscription->id,
            'status' => SubscriptionStatus::STATUS_ACTIVE
        ]);

        $status->save();
    }
}
