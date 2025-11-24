<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class SubscriptionPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function list(User $user)
    {
        return $user->permitted('subscription.list-all');
    }

    public function show(User $user, Subscription $subscription)
    {
        if ($user->permitted('subscription.show-any')) {
            return true;
        }

        return $user->permitted('subscription.show') && $user->id == $subscription->user_id;
    }

    public function store(User $user)
    {
        return $user->permitted('subscription.store');
    }

    public function update(User $user, Subscription $subscription)
    {
        $this->restrictDemo();

        if ($user->permitted('subscription.update-any')) return true;

        return $user->permitted('subscription.update') && $user->id == $subscription->user_id;
    }
}
