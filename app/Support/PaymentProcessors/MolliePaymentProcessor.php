<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Api\Mollie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MolliePaymentProcessor extends PaymentProcessor
{
    public function slug()
    {
        return 'mollie';
    }

    private function api()
    {
        return new Mollie($this->config('api_key'), $this->config('partner_id'), $this->config('partner_profile'));
    }

    protected function doTestCredentials(): bool
    {
        $methods = $this->api()->getPaymentMethods();

        return key_exists('count', $methods);
    }

    protected function makePayLink(Subscription $subscription)
    {
        $payment = $this->api()->createPayment(
            amount: $this->price($subscription),
            currency: $this->currencyManager->enabledCurrency()->currency_code,
            description: $this->planDescription($subscription),
            redirectUrl: $this->successUrl(),
            webhookUrl: $this->webhookUrl(),
            metadata: [
                'subscription_id' => $subscription->id
            ]
        );

        $checkoutLink = @$payment['_links']['checkout']['href'];

        if (empty($checkoutLink)) {
            Log::error('Mollie: Cannot get checkout link.' . json_encode($payment, JSON_PRETTY_PRINT));
            return null;
        }

        return $checkoutLink;
    }

    protected function verifyWebhook(Request $request): bool
    {
        $payment = $this->api()->getPayment($request->id);

        if (empty($payment)) {
            Log::error('Mollie payment not found. id = ' . $request->id);
            return false;
        }

        if (empty($payment['status'])) {
            Log::error('Mollie payment status is empty. ' . json_encode($payment, JSON_PRETTY_PRINT));
            return false;
        }

        return $payment['status'] == 'paid';
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $payment = $this->api()->getPayment($request->id);

        $subscription_id = @$payment['metadata']['subscription_id'];

        $subscription = Subscription::find($subscription_id);

        if (empty($subscription)) {
            Log::error('Could not resolve local subscription. ' . json_encode($payment, JSON_PRETTY_PRINT));
            return;
        }

        $this->subscriptionManager->activateSubscription($subscription);

        $this->createTransaction(
            remote_transaction_id: $payment['id'],
            subscription_id: $subscription_id,
            amount: $payment['amount']['value'],
            currency: $payment['amount']['currency'],
            status: Transaction::STATUS_SUCCESS
        );
    }
}
