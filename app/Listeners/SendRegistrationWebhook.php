<?php

namespace App\Listeners;

use App\Support\Webhooks\ClientRegistration;
use Illuminate\Auth\Events\Registered;

class SendRegistrationWebhook
{
    public function handle(Registered $event)
    {
        ClientRegistration::withUser(
            $event->user
        )
            ->dispatch();
    }
}
