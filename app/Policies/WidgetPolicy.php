<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Widget;
use Illuminate\Auth\Access\HandlesAuthorization;

class WidgetPolicy
{
    use HandlesAuthorization;

    public function list(User $user)
    {
        return $user->permitted('qrcode.list');
    }

    /**
     * Determine whether the user can show the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Widget  $widget
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function show(User $user, Widget $widget)
    {
        if ($user->permitted('qrcode.show-any')) {
            return true;
        }

        return $user->permitted('qrcode.show') && $widget->user_id == $user->id;;
    }

    /**
     * Determine whether the user can store models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function store(User $user)
    {
        return $user->permitted('qrcode.store');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Widget  $widget
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Widget $widget)
    {
        if ($user->permitted('qrcode.update-any')) {
            return true;
        }

        return $user->permitted('qrcode.update') && $widget->user_id == $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Widget  $widget
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function destroy(User $user, Widget $widget)
    {
        if ($user->permitted('qrcode.destroy-any')) {
            return true;
        }

        return $user->permitted('qrcode.destroy') && $widget->user_id == $user->id;
    }
}
