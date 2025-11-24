<?php

namespace App\Notifications\Dynamic;

use App\Models\User;
use App\Notifications\Dynamic\Interfaces\ShouldBroadcast;

class TrialExpired extends Base implements ShouldBroadcast
{
    public function slug()
    {
        return 'trial-expired';
    }

    public function shouldBroadcast(User $user): bool
    {
        if (!$this->subscriptions->userOnTrialPlan($user)) return false;

        if ($this->getLastSent($user)) return false;

        return $this->subscriptions->subscriptionIsExpired(
            $this->users->getCurrentSubscription($user)
        );
    }
}
