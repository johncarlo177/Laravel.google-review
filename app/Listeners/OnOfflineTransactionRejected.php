<?php

namespace App\Listeners;

use App\Events\OfflineTransactionRejected;
use App\Notifications\CustomerOfflineTransactionRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OnOfflineTransactionRejected
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
     * @param  \App\Events\OfflineTransactionRejected  $event
     * @return void
     */
    public function handle(OfflineTransactionRejected $event)
    {
        $event->transaction->subscription->user->notify(
            new CustomerOfflineTransactionRejected($event->transaction)
        );
    }
}
