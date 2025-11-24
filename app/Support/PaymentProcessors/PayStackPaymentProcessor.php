<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Api\PayStack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayStackPaymentProcessor extends PaymentProcessor
{
    public function slug()
    {
        return 'paystack';
    }

    public function api()
    {
        return new PayStack($this->config('public_key'), $this->config('secret_key'));
    }

    protected function doTestCredentials(): bool
    {
        return false;
    }

    protected function makePayLink(Subscription $subscription)
    {
        $transaction = $this->api()->createTransaction(
            amount: $this->price($subscription),
            email: $subscription->user->email,
            metadata: ['subscription_id' => $subscription->id],
            callback_url: $this->successUrl()
        );

        $url = @$transaction['data']['authorization_url'];

        if (empty($url)) {
            Log::error('Expected PayStack response with data.authorization_url set, but instead got: ' . json_encode($transaction, JSON_PRETTY_PRINT));
            return null;
        }

        return $url;
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $data = $request->data;

        $subscription_id = $data['metadata']['subscription_id'];

        if (empty($subscription_id)) {
            Log::error('Expected PayStack response with subscription_id in metadata field. ' . json_encode($request->all(), JSON_PRETTY_PRINT));
            return;
        }

        $subscription = Subscription::find($subscription_id);

        $this->subscriptionManager->activateSubscription($subscription);

        $this->createTransaction(
            remote_transaction_id: $data['id'],
            subscription_id: $subscription_id,
            amount: $data['amount'] / 100,
            currency: $data['currency'],
            status: Transaction::STATUS_SUCCESS
        );
    }

    protected function verifyWebhook(Request $request): bool
    {
        // Retrieve the request's body
        $input = @file_get_contents("php://input");

        // validate event do all at once to avoid timing attack
        if ($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $input, $this->config('secret_key'))) {
            Log::error('Signature is invalid');
            return false;
        }

        $event = $request->event;

        if ($event != 'charge.success') return false;

        return true;
    }
}
