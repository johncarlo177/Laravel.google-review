<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Api\TwoCheckout;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;

class TwoCheckoutPaymentProcessor extends PaymentProcessor
{
    use WriteLogs;

    public function slug()
    {
        return '2checkout';
    }

    protected function api()
    {
        return new TwoCheckout(
            testing: $this->config('mode') != 'production'
        );
    }

    protected function verifyWebhook(Request $request): bool
    {
        return $request->ORDERSTATUS === 'COMPLETE';
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $subscriptionId = $request->REFNOEXT;

        $subscription = Subscription::findOrFail($subscriptionId);

        $this->subscriptionManager->activateSubscription($subscription);

        $this->createTransaction(
            remote_transaction_id: $request->REFNO,
            subscription_id: $subscription->id,
            amount: $request->IPN_TOTALGENERAL,
            currency: $request->CURRENCY,
            status: Transaction::STATUS_SUCCESS
        );
    }

    protected function makePayLink(Subscription $subscription)
    {
        $planId = $this->getTwoCheckoutId(
            $subscription->subscription_plan
        );

        if (empty($planId)) return null;

        return $this->api()->generateBuyLink(
            twoCheckoutPlanId: $planId,
            ref: $subscription->id
        );
    }

    protected function doTestCredentials(): bool
    {
        return true;
    }

    private function twoCheckoutIdConfigKey($planId)
    {
        return '2checkout_id_for_plan_' . $planId;
    }

    private function getTwoCheckoutId(SubscriptionPlan $plan)
    {
        $code = $this->config($this->twoCheckoutIdConfigKey($plan->id));

        $code = str_replace(' ', '+', $code);

        return $code;
    }
}
