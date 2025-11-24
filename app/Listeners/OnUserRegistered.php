<?php

namespace App\Listeners;


use App\Support\UserRegistration\DefaultRole;
use App\Support\UserRegistration\DefaultSubscription;
use App\Support\WelcomeNotifier;
use Illuminate\Auth\Events\Registered;

class OnUserRegistered
{

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(Registered $event)
    {
        DefaultSubscription::withUser($event->user)->assign();

        DefaultRole::withUser($event->user)->assign();

        WelcomeNotifier::withUser($event->user)
            ->onRegistration()
            ->notifyIfNeeded();
    }
}
