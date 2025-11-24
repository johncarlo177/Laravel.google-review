<?php

namespace App\Notifications\Dynamic;

use App\Notifications\Dynamic\Interfaces\ShouldBroadcast;
use App\Support\System\Traits\ClassListLoader;
use Throwable;

class DynamicNotificationsManager
{
    use ClassListLoader;

    private function getDir()
    {
        return __DIR__;
    }

    private function getNamespace()
    {
        return __NAMESPACE__;
    }

    public static function broadcast()
    {
        $manager = new static;

        collect($manager->broadcastableNotifications())->each(
            function (Base $notification) {
                try {
                    $notification->dynamicBroadcast();
                } catch (Throwable $th) {
                    //
                }
            }
        );
    }

    public function broadcastableNotifications()
    {
        $notifications = $this->makeInstancesOfInstantiableClassesInCurrentDirectory();

        return collect($notifications)->filter(

            function ($notification) {
                return $notification instanceof ShouldBroadcast;
            }

        )->values();
    }
}
