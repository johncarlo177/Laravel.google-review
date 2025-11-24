<?php

namespace App\Policies;

use App\Models\LeadForm;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadFormPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function list(User $user)
    {
        return $user->permitted('lead-form.list');
    }

    /**
     * Determine whether the user can show the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LeadForm  $leadForm
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function show(User $user, LeadForm $leadForm)
    {
        if ($user->permitted('lead-form.show-any')) return true;

        return $user->permitted('lead-form.show') && $leadForm->user_id == $user->id;
    }

    /**
     * Determine whether the user can store models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function store(User $user)
    {
        return $user->permitted('lead-form.store');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\LeadForm  $leadForm
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, LeadForm $leadForm)
    {
        if ($user->permitted('lead-form.update-any')) return true;

        return $user->permitted('lead-form.update') && $leadForm->user_id == $user->id;
    }
}
