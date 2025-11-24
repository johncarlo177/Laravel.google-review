<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Api\PayULatam;
use App\Support\PaymentProcessors\Interfaces\SelfHostedPaymentProcessor;
use App\Support\PaymentProcessors\Traits\RendersSelfHostedRoutes;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class PayULatamPaymentProcessor extends PaymentProcessor implements SelfHostedPaymentProcessor
{
    use WriteLogs;
    use RendersSelfHostedRoutes;

    public function api()
    {
        return new PayULatam(
            mode: $this->config('mode'),
            accountId: $this->config('accountId'),
            apiKey: $this->config('apiKey'),
            merchantId: $this->config('merchantId')
        );
    }

    protected function doTestCredentials(): bool
    {
        return false;
    }

    public function slug()
    {
        return 'payu-latam';
    }

    protected function verifyWebhook(Request $request): bool
    {
        Log::debug(json_encode($request->all(), JSON_PRETTY_PRINT));

        return @$this->api()->verifyWebhook($request->all());
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $reference = $request->input('reference_sale');

        $subscriptionId = str_replace('subscription-', '', $reference);

        $subscription = Subscription::find($subscriptionId);

        $this->logDebugf('Subscription ID = %s', $subscriptionId);

        if (!$subscription) {
            $this->logErrorf('Subscription is not found. id = %s', $subscriptionId);
            return;
        } else {
            $this->logDebugf('Subscription is found');
        }

        $this->subscriptionManager->activateSubscription($subscription);

        $this->logDebugf('Subscription is activated');

        $this->createTransaction(
            remote_transaction_id: $request->input('transaction_id'),
            subscription_id: $subscriptionId,
            amount: $request->input('value'),
            currency: $request->input('currency'),
            status: Transaction::STATUS_SUCCESS
        );

        $this->logDebugf('Transaction is created');
    }

    public function getDataArray(Subscription $subscription)
    {
        return $this->api()->getDataArray(
            amount: $this->price($subscription),
            description: $this->planDescription($subscription),
            referenceCode: "subscription-$subscription->id",
            currency: $this->currencyManager->enabledCurrency()->currency_code,
            buyerEmail: $subscription->user->email,
            responseUrl: $this->successUrl(),
            confirmationUrl: $this->webhookUrl(),
        );
    }
}
