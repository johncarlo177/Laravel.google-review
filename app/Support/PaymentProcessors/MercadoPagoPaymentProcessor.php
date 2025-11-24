<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Api\MercadoPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoPaymentProcessor extends PaymentProcessor
{
    public function slug()
    {
        return 'mercadopago';
    }

    public function api()
    {
        return new MercadoPago($this->config('access_token'));
    }

    protected function makePayLink(Subscription $subscription)
    {
        $response = $this->api()->createPreference(
            title: $this->planName($subscription),
            quantity: 1,
            unitPrice: $this->price($subscription),
            metadata: [
                'subscription_id' => $subscription->id,
            ],
            notificationUrl: $this->webhookUrl(),
            successUrl: $this->successUrl(),
            failureUrl: $this->canceledUrl()
        );

        if (!isset($response['init_point'])) {
            Log::error('Expected MercadoPago response with init_point key.', compact('response'));
            return null;
        }

        return $response['init_point'];
    }

    protected function doTestCredentials(): bool
    {
        $payments = $this->api()->getPayments();

        return array_key_exists('paging', $payments);
    }

    protected function verifyWebhook(Request $request): bool
    {
        $type = $request->type;

        if ($type != 'payment') {
            Log::debug('webhoook type is not payment');
            return false;
        }

        $payment = $this->api()->getPayment(@$request->data['id']);

        if (empty($payment)) {
            Log::error('could not get payment entity, got ' . json_encode($payment, JSON_PRETTY_PRINT));
            return false;
        }

        if ($payment['status'] != 'approved') {
            Log::debug('Payment status is not approved ' . json_encode($payment, JSON_PRETTY_PRINT));
            return false;
        }

        Log::debug('Webhook is verified.');
        return true;
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $payment = $this->api()->getPayment($request->data['id']);

        $subscription_id = @$payment['metadata']['subscription_id'];

        if (empty($subscription_id)) {
            Log::error('Could not get subscription id from payment entity metadata.' . json_encode($payment, JSON_PRETTY_PRINT));
            return;
        }

        $this->subscriptionManager->activateSubscription(Subscription::find($subscription_id));

        $this->createTransaction(
            remote_transaction_id: $payment['id'],
            subscription_id: $subscription_id,
            amount: $payment['transaction_details']['net_received_amount'],
            currency: $payment['currency_id'],
            status: Transaction::STATUS_SUCCESS
        );
    }
}
