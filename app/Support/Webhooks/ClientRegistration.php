<?php

namespace App\Support\Webhooks;

use App\Models\User;


class ClientRegistration extends BaseDispatcher
{
    protected User $user;

    protected function event()
    {
        return 'client_registration';
    }

    public static function withUser(User $user)
    {
        $instance = new static;

        $instance->user = $user;

        return $instance;
    }

    protected function getPayloadType()
    {
        return 'User';
    }

    protected function getPayload()
    {
        $this->user->load('subscriptions', 'subscriptions.subscription_plan');

        $array = $this->user->toArray();

        unset($array['roles']);

        return $array;
    }
}
