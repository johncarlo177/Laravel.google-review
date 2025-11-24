<?php

namespace App\Repositories;

use App\Interfaces\CurrencyManager;
use App\Interfaces\PaymentGateway;
use App\Models\StripePaymentGatewayModel;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Throwable;

use App\Models\Config as ConfigModel;

/** @deprecated */
class StripePaymentGateway implements PaymentGateway
{
    private static StripeClient $stripe;

    private CurrencyManager $currencies;

    public function __construct(CurrencyManager $currencies)
    {
    }

    public function getAccessToken()
    {
    }

    private function getProductId(SubscriptionPlan $subscriptionPlan)
    {
    }

    public function saveSubscriptionPlan(SubscriptionPlan $subscriptionPlan, bool $forceSync = false)
    {
    }

    public function verifySubscription(Subscription $subscription)
    {
    }

    public static function boot()
    {
    }

    private function shouldRun()
    {
    }

    private static function bindConfiguration()
    {
    }

    public function registerWebhook()
    {
    }

    public function listWebhooks()
    {
    }

    public function clearWebhooks()
    {
    }

    public function generateCheckoutUrl(Subscription $subscription)
    {
    }

    public function getCheckoutSessionByPaymentIntent($id)
    {
    }

    public function getCheckoutSession($id)
    {
    }
}
