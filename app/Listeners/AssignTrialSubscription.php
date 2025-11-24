<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;

/**
 * @deprecated 
 * @see App\Listeners\OnUserRegistered
 */
class AssignTrialSubscription
{

    /**
     * Handle the event.
     *
     * @param  \App\Events\UserCreated  $event
     * @return void
     */
    public function handle(Registered $event) {}
}
