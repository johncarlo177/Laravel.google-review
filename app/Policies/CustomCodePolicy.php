<?php

namespace App\Policies;

use App\Models\CustomCode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomCodePolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function list(User $user)
    {
        return $user->permitted('custom-code.list-all');
    }

    /**
     * Determine whether the user can show the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CustomCode  $customCode
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function show(User $user, CustomCode $customCode)
    {
        return $user->permitted('custom-code.show-any');
    }

    /**
     * Determine whether the user can store models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function store(User $user)
    {
        $this->restrictDemo();

        return $user->permitted('custom-code.store');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CustomCode  $customCode
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, CustomCode $customCode)
    {
        $this->restrictDemo();

        return $user->permitted('custom-code.update-any');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\CustomCode  $customCode
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function destroy(User $user, CustomCode $customCode)
    {
        $this->restrictDemo();

        return $user->permitted('custom-code.destroy-any');
    }
}
