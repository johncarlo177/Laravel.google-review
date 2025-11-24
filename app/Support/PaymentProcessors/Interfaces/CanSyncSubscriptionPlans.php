<?php

namespace App\Support\PaymentProcessors\Interfaces;

use App\Models\SubscriptionPlan;

interface CanSyncSubscriptionPlans
{
    public function syncPlan(SubscriptionPlan $subscriptionPlan);

    public function syncPlans();

    public function yearlyInterval(): string;

    public function monthlyInterval(): string;
}
