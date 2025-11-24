<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Api\Dintero;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;


class DinteroPaymentProcessor extends PaymentProcessor
{
    use WriteLogs;

    public function slug()
    {
        return 'dintero';
    }

    protected function api()
    {
        return new Dintero(
            accountId: $this->config('accountId'),
            clientId: $this->config('clientId'),
            clientSecret: $this->config('clientSecret'),
            profileId: $this->config('profile_id') ?? 'default',
            vat: $this->config('vat') ?? 25,
            testing: $this->config('mode') != 'production',
        );
    }

    protected function verifyWebhook(Request $request): bool
    {
        return true;
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $merchangeReference = $request->merchant_reference;

        $sessionId = $request->sid;

        $subscriptionId = $this->getSubscriptionIdFromItemId($merchangeReference);

        $subscription = Subscription::find($subscriptionId);

        if (!$subscription) {
            return null;
        }

        $transactionId = $request->transaction_id;

        $this->subscriptionManager->activateSubscription($subscription);

        $this->createTransaction(
            remote_transaction_id: $transactionId,
            subscription_id: $subscriptionId,
            amount: $subscription->subscription_plan->price,
            currency: $this->enabledCurrencyCode(),
            status: Transaction::STATUS_SUCCESS,
        );
    }

    public function getWebhook(Request $request)
    {
        if (!$this->verifyWebhook($request)) {
            return;
        }

        return $this->handleVerifiedWebhook($request);
    }

    protected function makePayLink(Subscription $subscription)
    {
        $session = $this->api()->createSession(
            successUrl: $this->successUrl(),
            callbackUrl: $this->webhookUrl(),
            amount: $subscription->subscription_plan->price,
            currency: $this->enabledCurrencyCode(),
            itemId: $this->makeItemId($subscription),
            itemDescription: $subscription->subscription_plan->description,
            email: $subscription->user->email
        );

        if (!@$session['url']) {
            //

            $this->logWarningf(
                'Expected session response with URL, but got %s',
                json_encode($session, JSON_PRETTY_PRINT)
            );

            return null;
        }

        return $session['url'];
    }

    private function makeItemId(Subscription $subscription)
    {
        return sprintf(
            '%s-%s',
            $this->config('reference'),
            $subscription->id,
        );
    }


    private function getSubscriptionIdFromItemId($itemId)
    {
        return preg_replace('/[^\d]/', '', $itemId);
    }


    protected function doTestCredentials(): bool
    {
        return false;
    }
}
