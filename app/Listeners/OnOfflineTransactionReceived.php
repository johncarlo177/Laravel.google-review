<?php

namespace App\Listeners;

use App\Events\OfflineTransactionReceived;
use App\Notifications\AdminOfflineTransactionReceived;
use App\Notifications\CustomerOfflineTransactionReceived;
use App\Notifications\CustomerOfflineTransactionRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OnOfflineTransactionReceived extends Listener
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
     * @param  \App\Events\OfflineTransactionReceived  $event
     * @return void
     */
    public function handle(OfflineTransactionReceived $event)
    {
        $this->notifyAdmins(new AdminOfflineTransactionReceived($event->transaction));

        $event->transaction->subscription->user->notify(
            new CustomerOfflineTransactionReceived($event->transaction)
        );
    }
}
