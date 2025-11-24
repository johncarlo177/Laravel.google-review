<?php

namespace App\Listeners;

use App\Events\ConfigChanged;
use App\Events\ConfigWillChange;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OnConfigWillChange
{
    private static $listeners = [];

    public static function listen($callback)
    {
        static::$listeners[] = $callback;
    }

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
     * @param  \App\Events\ConfigWillChange  $event
     * @return void
     */
    public function handle(ConfigWillChange $event)
    {
        $key = $event->key;
        $value = $event->value;

        foreach ($this::$listeners as $callback) {
            call_user_func_array($callback, [$key, $value]);
        }
    }
}
