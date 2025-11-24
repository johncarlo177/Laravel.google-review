<?php

namespace App\Support;

use App\Models\SubscriptionPlan;

class SubscriptionPlansManager
{
    public function duplicate(SubscriptionPlan $subscriptionPlan)
    {
        $copy = new SubscriptionPlan($subscriptionPlan->toArray());

        $copy->save();

        return $copy;
    }
}
