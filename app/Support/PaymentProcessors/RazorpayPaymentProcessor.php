<?php

namespace App\Support\PaymentProcessors;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Support\PaymentProcessors\Api\Razorpay;
use App\Support\PaymentProcessors\Interfaces\CanSyncSubscriptionPlans;

use App\Support\PaymentProcessors\Traits\HandlesSyncSubscriptionPlans;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Log;


class RazorpayPaymentProcessor extends PaymentProcessor implements CanSyncSubscriptionPlans
{
    use WriteLogs;

    use HandlesSyncSubscriptionPlans;

    public function api(): Razorpay
    {
        return new Razorpay(
            $this->config('key_id'),
            $this->config('key_secret'),
            $this->config('webhook_secret')
        );
    }

    public function slug()
    {
        return 'razorpay';
    }

    protected function makePayLink(Subscription $subscription)
    {
        if ($this->isOneTime())
            return $this->makeOnetimeOrderPayLink($subscription);

        return $this->makeSubscriptionPayLink($subscription);
    }

    public function isRecurring()
    {
        if (empty($this->config('integration_type'))) {
            return true;
        }

        return $this->config('integration_type') === 'recurring';
    }

    public function isOneTime()
    {
        return $this->config('integration_type') === 'onetime';
    }

    private function makeOnetimeOrderPayLink(Subscription $subscription)
    {
        $response = $this->api()->createPaymentLink(
            amount: $this->price($subscription) * 100,
            currency: $this->currencyManager->enabledCurrency()->currency_code,
            reference_id: 'subscription_' . $subscription->id,
            description: $this->planDescription($subscription),
            customer_name: $subscription->user->name,
            customer_email: $subscription->user->email,
            callback_url: $this->successUrl(),
            notes: [
                'subscription_id' => $subscription->id
            ]
        );

        if (!isset($response['short_url'])) {
            if (!isset($response['short_url'])) {
                Log::error('Expected Razorpay payment link entity with short_url attribute. ', $response);
                return null;
            }
        }

        $subscription->setMeta($this->slug() . '_payment_link_id', $response['id']);

        return $response['short_url'];
    }

    private function makeSubscriptionPayLink(Subscription $subscription)
    {
        $response = $this->api()->createSubscription(
            plan_id: $this->getPlanId($subscription->subscription_plan),
            total_count: 1,
            notes: [
                'subscription_id' => $subscription->id
            ]
        );

        if (!isset($response['short_url'])) {
            Log::error('Expected Razorpay subscription entity with short_url attribute.', $response);
            return null;
        }

        $this->setSubscriptionId($subscription, $response['id']);

        return $response['short_url'];
    }

    protected function doTestCredentials(): bool
    {
        $plans = $this->api()->listPlans();

        $this->logger()->logInfo('Response = %s', json_encode($plans));

        return array_key_exists('items', $plans);
    }

    protected function syncSubscriptionPlan(SubscriptionPlan $plan): string
    {
        if ($this->isOneTime()) return 'Not syncing plans. One time mode is enabled.';

        $razorpayPlan = $this->api()->createPlan(
            period: $this->resolveInterval($plan),
            interval: 1,
            item_name: $plan->name,
            item_amount: $plan->price * 100,
            item_currency: $this->currencyManager->enabledCurrency()->currency_code,
            item_description: $plan->description
        );

        if (!isset($razorpayPlan['id'])) {
            Log::error('Expected Razorpay create plan response with valid id attribute, instead we got: ', $razorpayPlan);
            return '';
        }

        $this->setPlanId($plan, $razorpayPlan['id']);

        return $this->getPlanId($plan);
    }

    protected function setPlanId(SubscriptionPlan $plan, $id)
    {
        $plan->setMeta($this->slug() . '_plan_id', $id);
    }

    protected function getPlanId(SubscriptionPlan $subscriptionPlan)
    {
        return $subscriptionPlan->getMeta($this->slug() . '_plan_id');
    }

    protected function verifyWebhook(Request $request): bool
    {
        $this->logWarning($request->all());

        return true;

        if ($request->event != 'payment.captured') {
            return false;
        }

        $payment = $this->getPaymentEntityFromWebhookRequest($request);

        if (empty(@$payment['id'])) return false;

        return $payment['status'] === 'captured';
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $payment = $this->getPaymentEntityFromWebhookRequest($request);

        if ($this->isRecurring()) {
            $subscription = $this->getSubscriptionFromRecurringPayment($payment);
        } else if ($this->isOneTime()) {
            $subscription = $this->getSubscriptionFromOneTimePayment($payment);
        }

        if (empty($subscription)) {
            Log::error('Razorpay webhook, could not get local subscription record.');
            return;
        }

        $this->subscriptionManager->activateSubscription($subscription);

        $this->createTransaction(
            remote_transaction_id: $payment['id'],
            subscription_id: $subscription->id,
            amount: $payment['amount'] / 100,
            currency: $payment['currency'],
            status: Transaction::STATUS_SUCCESS
        );
    }

    private function getRemoteSubscriptionByPaymentId($payment_id)
    {
        $payment = $this->api()->getPayment($payment_id);

        $invoice = $this->api()->getInvoice(
            invoice_id: $payment['invoice_id']
        );

        $subscription = $this->api()->getSubscription(
            $invoice['subscription_id']
        );

        return $subscription;
    }

    private function getSubscriptionFromRecurringPayment($payment): ?Subscription
    {
        $subscription = @$this->getRemoteSubscriptionByPaymentId($payment['id']);

        $subscription_id = @$subscription['notes']['subscription_id'];

        if (empty($subscription_id)) {
            Log::error('Razorpay webhook: cannot get subscription_id from Razorpay subscription entity, got', compact('subscription'));
            return null;
        }

        return Subscription::find($subscription_id);
    }

    private function getSubscriptionFromOneTimePayment($payment): ?Subscription
    {
        $id = @$payment['order_id'];

        if (!$id) {
            return null;
        }

        $order = $this->api()->getOrder(@$payment['order_id']);

        $subscription_id = str_replace('subscription_', '', $order['receipt']);

        return Subscription::find($subscription_id);
    }

    protected function remoteTransactionIdMetaKey()
    {
        return 'payment';
    }

    private function getPaymentEntityFromWebhookRequest(Request $request)
    {
        $paymentId = @$request->payload['payment']['entity']['id'];

        if (empty($paymentId)) return null;

        $payment = @$this->api()->getPayment(
            $paymentId
        );

        return $payment;
    }

    public function yearlyInterval(): string
    {
        return 'yearly';
    }

    public function monthlyInterval(): string
    {
        return 'monthly';
    }
}
