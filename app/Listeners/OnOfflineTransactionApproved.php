<?php

namespace App\Listeners;

use App\Events\OfflineTransactionApproved;
use App\Notifications\CustomerOfflineTransactionApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OnOfflineTransactionApproved
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
     * @param  \App\Events\OfflineTransactionApproved  $event
     * @return void
     */
    public function handle(OfflineTransactionApproved $event)
    {
        $event->transaction->subscription->user->notify(
            new CustomerOfflineTransactionApproved($event->transaction)
        );
    }
}
