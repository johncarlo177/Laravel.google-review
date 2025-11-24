<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;
use App\Policies\Restriction\FileRestrictor;
use Illuminate\Auth\Access\HandlesAuthorization;

class FilePolicy
{
    use HandlesAuthorization;

    public function show(User $user, File $file)
    {
        if ($user->permitted('file.show-any')) {
            return true;
        }

        return $user->permitted('file.show') && $user->id == $file->user_id;
    }

    /**
     * Determine whether the user can store models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function store(User $user)
    {
        return $user->permitted('file.show');
    }

    public function destroy(User $user, File $file)
    {
        FileRestrictor::make($file->id)->applyRestrictions();

        if ($user->permitted('file.destroy-any')) {
            return true;
        }

        return $user->permitted('file.destroy') && $user->id == $file->user_id;
    }
}
