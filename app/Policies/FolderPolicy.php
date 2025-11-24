<?php

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Auth\Access\HandlesAuthorization;

class FolderPolicy extends BasePolicy
{
    use WriteLogs;

    use HandlesAuthorization;

    private User $actor;

    public static function forActor(User $actor)
    {
        $instance = new static;

        $instance->actor = $actor;

        return $instance;
    }

    public function list(User $user)
    {
        if ($this->actor->isSuperAdmin()) return true;

        return $this->actor->permitted('folder.list') && $this->actor->id == $user->id;
    }

    public function show(User $owner, Folder $folder)
    {
        if ($this->actor->isSuperAdmin()) return true;

        return $owner->id == $folder->user_id;
    }

    /**
     * Determine whether the user can store models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function store(User $owner)
    {
        $this->restrictDemo();

        if ($this->actor->isSuperAdmin()) return true;

        return $this->actor->permitted('folder.store') && $this->actor->id == $owner->id;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Folder  $folder
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $owner, Folder $folder)
    {
        if ($this->actor->isSuperAdmin()) return true;

        return $this->actor->permitted('folder.update') &&
            $this->actor->id == $owner->id &&
            $folder->user_id == $owner->id;
    }


    public function destroy(User $owner, Folder $folder)
    {
        $this->restrictDemo();

        if ($this->actor->isSuperAdmin()) return true;

        return $this->actor->permitted('folder.destroy') &&
            $this->actor->id == $owner->id &&
            $folder->user_id == $owner->id;
    }
}
