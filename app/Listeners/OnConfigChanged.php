<?php

namespace App\Listeners;

use App\Events\ConfigChanged;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OnConfigChanged extends Listener
{

    protected static $listeners = [];

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
     * @param  \App\Events\ConfigChanged  $event
     * @return void
     */
    public function handle(ConfigChanged $event)
    {
        $key = $event->key;

        foreach ($this::$listeners as $callback) {
            call_user_func_array($callback, [$key]);
        }
    }
}
