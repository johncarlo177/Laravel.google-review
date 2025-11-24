<?php

namespace App\Listeners;

use App\Support\System\Traits\WriteLogs;
use Exception;
use Illuminate\Support\Facades\Log;

abstract class DynamicListener extends Listener
{
    use WriteLogs;

    protected static $_listeners = [];

    public static function listen($callback)
    {
        static::logListeners('Adding event listener');

        static::addListener($callback);

        static::logListeners('Event listener added');
    }

    private static function logListeners($message)
    {
    }

    private static function listeners()
    {
        if (!isset(static::$_listeners[static::class])) {
            static::$_listeners[static::class] = [];
        }

        return static::$_listeners[static::class];
    }

    private static function addListener($callback)
    {
        static::$_listeners[static::class][] = $callback;
    }

    /**
     * Handle the event.
     *
     * @param $event
     * @return void
     */
    public function handle($event)
    {
        $this::logListeners('Calling regsitered listeners');

        foreach ($this::listeners() as $callback) {
            call_user_func_array($callback, [$event]);
        }
    }
}
