<?php

namespace App\Listeners;

use App\Interfaces\UserManager;
use stdClass;

abstract class Listener
{
    protected function notifyAdmins($notification)
    {
        $users = app(UserManager::class);

        $users->getSuperAdmins()->each(function ($user) use ($notification) {
            $user->notify($notification);
        });
    }

    protected function getListenerPayloadFromEvent(stdClass $event)
    {
        return $event;
    }
}
