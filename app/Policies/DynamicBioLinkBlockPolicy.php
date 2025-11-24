<?php

namespace App\Policies;

use App\Models\DynamicBioLinkBlock;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class DynamicBioLinkBlockPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function list(User $user)
    {
        return $user->permitted('dynamic-biolink-block.list-all');
    }

    /**
     * Determine whether the user can show the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DynamicBioLinkBlock  $dynamicBioLinkBlock
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function show(User $user, DynamicBioLinkBlock $dynamicBioLinkBlock)
    {

        return $user->permitted('dynamic-biolink-block.show-any');
    }

    /**
     * Determine whether the user can store models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function store(User $user)
    {
        return $user->permitted('dynamic-biolink-block.store');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DynamicBioLinkBlock  $dynamicBioLinkBlock
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, DynamicBioLinkBlock $dynamicBioLinkBlock)
    {
        return $user->permitted('dynamic-biolink-block.update-any');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\DynamicBioLinkBlock  $dynamicBioLinkBlock
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function destroy(User $user, DynamicBioLinkBlock $dynamicBioLinkBlock)
    {
        return $user->permitted('dynamic-biolink-block.destroy-any');
    }
}
