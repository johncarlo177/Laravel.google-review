<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use App\Interfaces\SubscriptionManager;
use App\Models\Subscription;

class SubscriptionSeeder extends Seeder
{
    protected $version = 'v2.33';

    protected SubscriptionManager $subscriptions;

    public function __construct()
    {
        $this->subscriptions = app(SubscriptionManager::class);
    }

    protected function run()
    {
        $this->setExpirationDateOfAllSubscriptions();
    }

    protected function setExpirationDateOfAllSubscriptions()
    {
        $subscriptions = Subscription::get();

        $subscriptions->each(function (Subscription $subscription) {
            $subscription->expires_at = $this->subscriptions->calculateExpiresAt($subscription);
            $subscription->save();
        });
    }
}
