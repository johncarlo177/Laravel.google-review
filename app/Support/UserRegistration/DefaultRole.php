<?php

namespace App\Support\UserRegistration;

use App\Models\Role;
use App\Models\User;

class DefaultRole
{
    protected User $user;

    public static function withUser(User $user)
    {
        $instance = new static;

        $instance->user = $user;

        return $instance;
    }

    public function assign()
    {
        $role = Role::where('is_default_role_for_new_signup', true)->first();

        $this->user->roles()->save($role);
    }
}
