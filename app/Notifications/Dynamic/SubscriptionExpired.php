<?php

namespace App\Notifications\Dynamic;

use App\Models\User;
use App\Notifications\Dynamic\Interfaces\ShouldBroadcast;

class SubscriptionExpired extends Base implements ShouldBroadcast
{
    public function slug()
    {
        return 'subscription-expired';
    }

    public function shouldBroadcast(User $user): bool
    {
        $subscription = $this->users->getCurrentSubscription($user);

        if ($this->subscriptions->userOnTrialPlan($user)) return false;

        if (!$subscription) return false;

        if ($subscription->id == $this->getLastSentSubscriptionId($user)) return false;

        return $this->subscriptions->subscriptionIsExpired($subscription);
    }

    protected function afterNotify(User $user)
    {
        $subscription = $this->users->getCurrentSubscription($user);

        $this->setLastSentSubscriptionId($user, $subscription->id);
    }

    private function  setLastSentSubscriptionId(User $user, $id)
    {
        return $this->setUserMeta($user, 'last-sent-subscription-id', $id);
    }

    private function getLastSentSubscriptionId(User $user)
    {
        return $this->getUserMeta($user, 'last-sent-subscription-id');
    }
}
