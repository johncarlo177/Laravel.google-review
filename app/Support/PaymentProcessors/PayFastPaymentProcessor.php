<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Api\PayFast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayFastPaymentProcessor extends PaymentProcessor
{
    public function slug()
    {
        return 'payfast';
    }

    public function api()
    {
        return new PayFast(
            $this->config('mode'),
            $this->config('merchant_id'),
            $this->config('merchant_key'),
            $this->config('passphrase')
        );
    }

    protected function doTestCredentials(): bool
    {
        return true;
    }

    protected function makePayLink(Subscription $subscription)
    {
        return $this->api()->createPaymentLink(
            item_name: $this->planDescription($subscription),
            amount: $this->price($subscription),
            email: $subscription->user->email,
            return_url: $this->successUrl(),
            cancel_url: $this->canceledUrl(),
            notify_url: $this->webhookUrl(),
            custom_str1: json_encode(['subscription_id' => $subscription->id]),
        );
    }

    protected function remoteTransactionIdMetaKey()
    {
        return 'pf_payment';
    }

    protected function verifyWebhook(Request $request): bool
    {
        return $this->api()->verifyWebhookData($request->all());
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $subscription_id = @json_decode($request->custom_str1, true)['subscription_id'];

        if (empty($subscription_id)) {
            Log::error('Subscription id is empty' . json_encode($request->all(), JSON_PRETTY_PRINT));
            return;
        }

        $this->subscriptionManager->activateSubscription(Subscription::find($subscription_id));

        $this->createTransaction(
            remote_transaction_id: $request->pf_payment_id,
            subscription_id: $subscription_id,
            amount: $request->amount_net,
            currency: $this->currencyManager->enabledCurrency()->currency_code,
            status: Transaction::STATUS_SUCCESS
        );
    }
}
