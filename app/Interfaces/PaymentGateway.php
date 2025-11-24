<?php

namespace App\Interfaces;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;

interface PaymentGateway
{
    public function getAccessToken();

    public function saveSubscriptionPlan(SubscriptionPlan $subscriptionPlan, bool $forceSync = false);

    public function verifySubscription(Subscription $subscription);

    public static function boot();

    public function registerWebhook();

    public function listWebhooks();
}
