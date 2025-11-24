<?php

namespace App\Support\System;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminHelper
{
    public static function makeAdminAccount($name, $email, $password)
    {
        if (!($user = User::whereEmail($email)->first())) {
            $user = new User();
        }

        $user->name = $name;

        $user->email = $email;

        $user->password = Hash::make($password);

        $user->email_verified_at = now();

        $user->save();

        $user->setRole(Role::where('super_admin', true)->first());

        return sprintf('Success [%s] at %s', $user->id, $user->created_at->format('Y-m-d H:i:s'));
    }
}
