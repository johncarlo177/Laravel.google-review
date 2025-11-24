<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Api\PayUInternational;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayUInternationalPaymentProcessor extends PaymentProcessor
{
    public function api()
    {
        return new PayUInternational(
            $this->config('mode'),
            $this->config('pos_id'),
            $this->config('second_key'),
            $this->config('client_id'),
            $this->config('client_secret')
        );
    }

    protected function doTestCredentials(): bool
    {

        return !empty(@$this->api()->getToken());
    }

    public function slug()
    {
        return 'payu-international';
    }

    protected function verifyWebhook(Request $request): bool
    {
        $orderId = @$request->order['orderId'];

        if (empty($orderId)) return false;

        return $orderId == $this->api()->getOrder($orderId)['orderId'] && $request->order['status'] === 'COMPLETED';
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $subscription_id = json_decode($request->order['extOrderId'], true)['subscription_id'];

        $this->subscriptionManager->activateSubscription(Subscription::find($subscription_id));

        $paymentId = collect($request->properties)->first(fn ($property) => $property['name'] == 'PAYMENT_ID')['value'];

        $this->createTransaction(
            remote_transaction_id: $paymentId,
            subscription_id: $subscription_id,
            amount: $request->order['totalAmount'] / 100,
            currency: $request->order['currencyCode'],
            status: Transaction::STATUS_SUCCESS
        );
    }

    protected function makePayLink(Subscription $subscription)
    {
        $order = $this->api()->createOrder(
            notifyUrl: $this->webhookUrl(),
            continueUrl: $this->successUrl(),
            customerIp: request()->ip(),
            description: $this->planDescription($subscription),
            currencyCode: $this->currencyManager->enabledCurrency()->currency_code,
            totalAmount: $this->price($subscription),
            extOrderId: json_encode(['subscription_id' => $subscription->id]),
            buyer_email: $subscription->user->email,
            product_name: $this->planName($subscription),
            product_unitPrice: $this->price($subscription),
            product_quantity: 1
        );

        if (isset($order['redirectUri'])) {
            return $order['redirectUri'];
        }

        Log::warning('Expected PayU order object with redirectUri, got ', compact('order'));

        return null;
    }
}
