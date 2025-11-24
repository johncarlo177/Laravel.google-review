<?php

namespace App\Listeners;

use App\Events\ContactReceived;
use App\Interfaces\UserManager;
use App\Notifications\AdminContactReceived;
use App\Notifications\CustomerContactReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OnContactReceived
{
    private UserManager $users;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(UserManager $users)
    {
        $this->users = $users;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ContactReceived  $event
     * @return void
     */
    public function handle(ContactReceived $event)
    {
        Notification::route('mail', [
            $event->contact->email => $event->contact->name,
        ])->notify(new CustomerContactReceived($event->contact));


        $this->users->getSuperAdmins()->each(function ($user) use ($event) {
            $user->notify(new AdminContactReceived($event->contact));
        });
    }
}
