<?php

namespace App\Listeners;

use App\Events\SavingSubscriptionPlan;


/** @deprecated */
class OnSavingSubscriptionPlan
{


    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\SavingSubscriptionPlan  $event
     * @return void
     */
    public function handle(SavingSubscriptionPlan $event)
    {
    }

    private function shouldHandle()
    {
    }
}
