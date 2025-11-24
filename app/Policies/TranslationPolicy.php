<?php

namespace App\Policies;

use App\Models\Translation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TranslationPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function list(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can show the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Translation  $translation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function show(User $user, Translation $translation)
    {
        return $user->permitted('translation.show-any');
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

        return $user->permitted('translation.store');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Translation  $translation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Translation $translation)
    {
        $this->restrictDemo();

        return $user->permitted('translation.update-any');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Translation  $translation
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function destroy(User $user, Translation $translation)
    {
        $this->restrictDemo();

        return $user->permitted('translation.destroy-any');
    }
}
