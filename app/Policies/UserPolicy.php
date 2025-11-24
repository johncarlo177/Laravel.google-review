<?php

namespace App\Policies;

use App\Http\Middleware\ErrorMessageMiddleware;
use App\Interfaces\SubscriptionManager;
use App\Models\User;
use App\Support\DropletManager;
use App\Support\System\Traits\WriteLogs;
use App\Support\UserSearchBuilder;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy extends BasePolicy
{
    use WriteLogs;

    use HandlesAuthorization;

    private DropletManager $droplet;

    private SubscriptionManager $subscriptions;

    public function __construct()
    {
        $this->droplet = new DropletManager;

        $this->subscriptions = app(SubscriptionManager::class);
    }

    public function list(User $actor)
    {
        return $actor->permitted('user.list-all');
    }

    public function show(User $actor, User $subject)
    {
        if ($actor->permitted('user.show-any')) {
            return UserSearchBuilder::withActor($actor)->canAccess($subject);
        }

        return $actor->id == $subject->id;
    }

    public function store(User $actor)
    {
        return $actor->permitted('user.store');
    }

    public function update(User $actor, User $subject)
    {
        $this->restrictDemo();

        // Allow users to modify their own account.
        if ($actor->id === $subject->id) {
            return true;
        }

        // Allow admin to modify any regular users.
        if (!$subject->permitted('user.destroy-any')) {
            return $actor->permitted('user.destroy-any');
        }

        return $actor->permitted('user.update-any') &&
            $this->verifyCreatedBefore($actor, $subject) &&
            UserSearchBuilder::withActor($actor)->canAccess($subject);
    }

    public function forceVerifyEmail(User $user)
    {
        return $user->permitted('user.update-any');
    }

    public function inviteSubUser(User $actor, User $parentUser)
    {
        if ($this->droplet->isSmall()) return false;

        if (!$actor->permitted('user.invite')) return false;

        if ($this->subscriptions->userInvitedUsersLimitReached($parentUser)) {

            ErrorMessageMiddleware::abortWithMessage(
                t('Sub users limit reached')
            );
        }

        if ($actor->isSuperAdmin()) return true;

        return $actor->id == $parentUser->id;
    }

    public function listSubUsers(User $actor, User $parentUser)
    {
        if ($actor->isSuperAdmin()) return true;

        return $actor->id == $parentUser->id;
    }

    public function deleteSubUser(User $actor, User $parentUser, User $subUser)
    {
        if ($actor->isSuperAdmin()) return true;

        return $actor->id == $parentUser->id;
    }

    public function destroy(User $actor, User $subject)
    {
        $this->restrictDemo();

        if ($actor->id == $subject->id) {
            // 
            if ($subject->isSuperAdmin()) {
                return false;
            }
            // Allow customers to delete their own account.
            return true;
        }

        // Allow admin to delete any regular users.
        if (!$subject->permitted('user.destroy-any')) {
            return $actor->permitted('user.destroy-any');
        }

        return $actor->permitted('user.destroy-any') &&
            $subject->id != $actor->id &&
            $this->verifyCreatedBefore($actor, $subject) &&
            UserSearchBuilder::withActor($actor)->canAccess($subject);
    }

    public function changeAccountBalance(User $actor, User $subject)
    {
        $this->restrictDemo();

        return $actor->permitted('user.change-any-account-balance');
    }

    public function getAccountBalance(User $actor, User $subject)
    {
        return $actor->permitted('user.get-account-balance');
    }

    private function verifyCreatedBefore(User $actor, User $subject)
    {
        // This is for admins, older super admin can delete or update other admins, but recent one cannot delete.

        return $actor->created_at->isBefore($subject->created_at);
    }
}
