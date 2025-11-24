<?php

namespace App\Support\UserRegistration;

use App\Interfaces\SubscriptionManager;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Plugins\PluginManager;

class DefaultSubscription
{
    protected User $user;

    protected SubscriptionManager $subscriptions;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->subscriptions = app(SubscriptionManager::class);
    }

    public static function withUser(User $user)
    {
        $instance = new static;

        $instance->user = $user;

        return $instance;
    }

    protected function getDefaultPlan()
    {
        $trial = SubscriptionPlan::where('is_trial', true)
            ->first();

        if (!$trial) {
            $trial = $this->getFallbackPlan();
        }

        $defaultPlan = $trial;

        $defaultPlan = PluginManager::doFilter(
            name: PluginManager::FILTER_DEFAULT_SUBSCRIPTION_PLAN,
            value: $defaultPlan
        );

        return $defaultPlan;
    }

    protected function getFallbackPlan()
    {
        $freePlan = SubscriptionPlan::where('price', 0)
            ->first();

        return $freePlan;
    }

    public function assign()
    {
        $plan = $this->getDefaultPlan();

        if (!$plan) {
            return;
        }

        $subscription = $this->subscriptions->createSubscription(
            user: $this->user,
            plan: $plan
        );

        $this->subscriptions->activateSubscription($subscription);
    }
}
