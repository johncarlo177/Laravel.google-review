<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Api\YooKassa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YooKassaPaymentProcessor extends PaymentProcessor
{
    protected function doTestCredentials(): bool
    {
        return false;
    }

    public function api()
    {
        return new YooKassa(
            $this->config('client_id'),
            $this->config('client_secret')
        );
    }

    public function slug()
    {
        return 'yookassa';
    }

    protected function makePayLink(Subscription $subscription)
    {
        return $this->api()->createPayLink(
            amount: $this->price($subscription),
            description: $this->planDescription($subscription),
            payer_email: $subscription->user->email,
            return_url: $this->successUrl(),
            metadata: [
                'subscription_id' => $subscription->id,
            ]
        );
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $subscription_id = $request->object['metadata']['subscription_id'];

        $subscription = Subscription::find($subscription_id);

        $this->subscriptionManager->activateSubscription($subscription);

        $this->createTransaction(
            remote_transaction_id: '',
            subscription_id: $subscription_id,
            amount: $subscription->subscription_plan->price,
            currency: 'RUB',
            status: Transaction::STATUS_SUCCESS
        );
    }

    protected function verifyWebhook(Request $request): bool
    {
        Log::debug($request->all());

        // TODO: verify YooKassa webhook with their API.
        return $request->event == 'payment.succeeded' && $request->object['status'] == 'succeeded';
    }
}
