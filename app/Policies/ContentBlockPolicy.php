<?php

namespace App\Policies;

use App\Models\ContentBlock;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContentBlockPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function list(User $user)
    {
        return $user->permitted('ContentBlock.list-all');
    }

    /**
     * Determine whether the user can show the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ContentBlock  $contentBlock
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function show(User $user, ContentBlock $contentBlock)
    {
        return $user->permitted('ContentBlock.show-any');
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

        return $user->permitted('ContentBlock.store');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ContentBlock  $contentBlock
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ContentBlock $contentBlock)
    {
        $this->restrictDemo();

        return $user->permitted('ContentBlock.update-any');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ContentBlock  $contentBlock
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function destroy(User $user, ContentBlock $contentBlock)
    {
        $this->restrictDemo();

        return $user->permitted('ContentBlock.destroy-any');
    }

    public function deleteAllBlocks(User $user)
    {
        $this->restrictDemo();

        return $user->permitted('ContentBlock.deleteAllBlocks');
    }

    public function copyAllBlocks(User $user)
    {
        $this->restrictDemo();

        return $user->permitted('ContentBlock.deleteAllBlocks');
    }
}
