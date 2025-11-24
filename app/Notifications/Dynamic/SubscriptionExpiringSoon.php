<?php

namespace App\Notifications\Dynamic;

use App\Models\User;
use App\Notifications\Dynamic\Interfaces\ShouldBroadcast;

class SubscriptionExpiringSoon extends Base implements ShouldBroadcast
{
    private $defaultRemainingDays = 3;

    public function slug()
    {
        return 'subscription-expiring-soon';
    }

    public function shouldBroadcast(User $user): bool
    {
        $subscription = $this->users->getCurrentSubscription($user);

        if ($this->subscriptions->userOnTrialPlan($user)) return false;

        if (!$subscription) return false;

        if ($this->getLastSent($user)) return false;

        $days = $this->subscriptions->getSubscriptionRemainingDays($subscription);

        if ($days === null) return false;

        return $days <= $this->remainingDays();
    }

    private function remainingDays()
    {
        $d = $this->config('remaining_days');

        return is_nan($d) || $d <= 0 ? $this->defaultRemainingDays : $d;
    }
}
