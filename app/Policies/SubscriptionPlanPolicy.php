<?php

namespace App\Policies;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscriptionPlanPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function list(User $actor)
    {
        return $actor->permitted('subscription-plan.list-all');
    }

    public function show(User $actor, SubscriptionPlan $subscriptionPlan)
    {
        return $actor->permitted('subscription-plan.show-any');
    }

    public function store(User $actor)
    {
        $this->restrictDemo();

        return $actor->permitted('subscription-plan.store');
    }

    public function update(User $actor, SubscriptionPlan $subscriptionPlan)
    {
        $this->restrictDemo();

        return $actor->permitted('subscription-plan.update-any');
    }

    public function duplicate(User $user, SubscriptionPlan $subscriptionPlan)
    {
        $this->restrictDemo();

        return $user->permitted('subscription-plan.update-any');
    }

    public function destroy(User $actor, SubscriptionPlan $subscriptionPlan)
    {
        $this->restrictDemo();

        return $actor->permitted('subscription-plan.destroy-any');
    }
}
