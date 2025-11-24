<?php

namespace App\Listeners;

use App\Models\Role;
use Illuminate\Auth\Events\Registered;

/**
 * @deprecated
 * @see App\Listeners\OnUserRegistered
 */
class AssignDefaultRole
{
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
     * @param  \App\Events\Registered  $event
     * @return void
     */
    public function handle(Registered $event) {}
}
