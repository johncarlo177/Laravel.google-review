<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Api\PhonePEDriver;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PhonePEPaymentProcessor extends PaymentProcessor
{
    use WriteLogs;

    public function slug()
    {
        return 'phonepe';
    }

    protected function driver()
    {
        $this->logDebug('mode=  %s', $this->config('mode'));

        $driver = new PhonePEDriver(
            client_id: $this->config('client_id'),
            client_version: $this->config('client_version'),
            client_secret: $this->config('client_secret'),
            mode: $this->config('mode')
        );

        return $driver;
    }

    protected function makePayLink(Subscription $subscription)
    {
        return $this->driver()->createPaymentLink(
            amount: $subscription->subscription_plan->price * 100,
            merchantId: sprintf('subscription-%s', $subscription->id),
            redirectUrl: $this->successUrl()
        );
    }

    protected function verifyWebhook(Request $request): bool
    {
        return false;
    }

    protected function doTestCredentials(): bool
    {
        return false;
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $event = $request->input('event');

        $payload = $request->input('payload');

        $payload = (array) $payload;

        if ($event !== 'checkout.order.completed') {
            return;
        }

        $subscriptionId = str_replace(
            'subscription-',
            '',
            $payload['merchantOrderId']
        );

        $subscription = Subscription::find($subscriptionId);

        $this->subscriptionManager->activateSubscription($subscription);

        $this->createTransaction(
            remote_transaction_id: $payload['orderId'],
            subscription_id: $subscriptionId,
            amount: $payload['amount'] / 100,
            currency: $this->currencyCode(),
            status: Transaction::STATUS_SUCCESS,
        );
    }
}
