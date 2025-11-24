<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function list(User $user)
    {
        // When manage has the ability to manage users, he should be able
        // to see list of roles to select the user role.
        return $user->permitted('role.list-all') || $user->permitted('user.list-all');
    }

    public function show(User $user, Role $role)
    {
        return true;
    }

    public function store(User $actor)
    {
        return $actor->permitted('role.store');
    }

    public function update(User $actor, Role $role)
    {
        return $actor->permitted('role.update-any');
    }

    public function destroy(User $actor, Role $role)
    {
        $this->restrictDemo();

        return $actor->permitted('role.destroy-any');
    }
}
