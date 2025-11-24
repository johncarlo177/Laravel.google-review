<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Support\PaymentProcessors\Api\PayPal;
use App\Support\PaymentProcessors\Interfaces\ActivatesSubscriptionOnReturnUrl;
use App\Support\PaymentProcessors\Interfaces\CanSyncSubscriptionPlans;
use App\Support\PaymentProcessors\Interfaces\HasCustomThankYouPage;
use App\Support\PaymentProcessors\Interfaces\ProcessAccountCreditBilling;
use App\Support\PaymentProcessors\Interfaces\RegistersWebhook;
use App\Support\PaymentProcessors\Traits\HandlesAccountCreditBilling;
use App\Support\PaymentProcessors\Traits\HandlesReturnUrl;
use App\Support\PaymentProcessors\Traits\HandlesSyncSubscriptionPlans;
use App\Support\System\Traits\WriteLogs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayPalPaymentProcessor extends PaymentProcessor

implements
    CanSyncSubscriptionPlans,
    ActivatesSubscriptionOnReturnUrl,
    RegistersWebhook,
    ProcessAccountCreditBilling,
    HasCustomThankYouPage
{
    use WriteLogs;
    use HandlesReturnUrl;
    use HandlesSyncSubscriptionPlans;
    use HandlesAccountCreditBilling;

    private static ?PayPal $_api = null;


    public function __construct()
    {
        parent::__construct();
    }

    public function shouldRenderCustomThankYouPage(): bool
    {
        return $this->billing->isAccountCreditBilling();
    }

    private function shouldHandleReturnUrl()
    {
        return $this->billing->isSubscriptionBilling();
    }

    protected function doTestCredentials(): bool
    {
        $token = $this->api()->getAccessToken();

        return !empty($token);
    }

    public function makeApi()
    {
        return new PayPal(
            mode: $this->config('mode') ?: 'sandbox',
            clientId: $this->config('client_id'),
            clientSecret: $this->config('client_secret')
        );
    }

    public function api()
    {
        if (!$this::$_api) {
            $this::$_api = $this->makeApi();
        }

        return $this::$_api;
    }

    public function slug()
    {
        return 'paypal';
    }

    public function createChargeLink(User $user, $amount)
    {
        $this->logDebugf('creating charge link for $%s', $amount);

        $response = $this->makeApi()->createOrder(
            $amount,
            $this->currencyManager->enabledCurrency()->currency_code,
            $this->accountCreditOrderDescription(),
            $user->id,
            $this->successUrl(),
            $this->canceledUrl(),
            config('app.name')
        );

        $this->logDebugf('Got PayPal response, %s', json_encode($response, JSON_PRETTY_PRINT));

        $links = collect($response['links']);

        $link = $links->first(fn($l) => $l['rel'] == 'payer-action') ?? [];

        return @$link['href'];
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        if ($this->billing->isAccountCreditBilling()) {
            return $this->handleAccountCreditWebhook($request);
        }

        return $this->handleSubscriptionModeWebhook($request);
    }

    private function handleAccountCreditWebhook(Request $request)
    {
        $this->logDebug(json_encode($request->all(), JSON_PRETTY_PRINT));

        $type = $request->event_type;

        switch ($type) {
            case 'CHECKOUT.ORDER.APPROVED':
                $orderId = $request->resource['id'];

                $userId = $request->resource['purchase_units'][0]['custom_id'];

                $amount = $request->resource['purchase_units'][0]['amount']['value'];

                $order = $this->makeApi()->captureOrder($orderId);

                if (@$order['status'] === 'COMPLETED') {

                    $this->accountCredit->forUser($userId)->addAccountBalance($amount);

                    $this->transactionManager->createTransaction(
                        subscription_id: null,
                        amount: $amount,
                        currency: $this->currencyManager->enabledCurrency()->currency_code,
                        source: $this->slug(),
                        status: Transaction::STATUS_SUCCESS,
                        user_id: $userId,
                        description: $this->accountCreditOrderDescription()
                    );

                    $this->logInfo('PayPal order captured successfully. ' . $orderId);
                } else {
                    $this->logErrorf('Cannot capture paypal order %s', json_encode($order, JSON_PRETTY_PRINT));
                }

                break;

            default:
                # code...
                break;
        }
    }

    private function handleSubscriptionModeWebhook(Request $request)
    {
        $type = $request->event_type;

        if (!preg_match('/payment|checkout/i', $type)) {
            return;
        }

        $this->logDebug('Paypal data = %s', $request->all());

        if ($type === 'CHECKOUT.ORDER.APPROVED') {
            $subscription_id = $request->resource['purchase_units'][0]['custom_id'];
        } else {
            $subscription_id = @$request->resource['custom'];
        }

        if (!$subscription_id) {
            return $this->logErrorf('Subscription ID is not present in PayPal request %s', json_encode($request->all(), JSON_PRETTY_PRINT));
        }

        $subscription = Subscription::find($subscription_id);


        if (!$subscription) {
            return $this->logErrorf('Subscription not found for ID [%s]', $subscription_id);
        }

        switch ($type) {
            case 'CHECKOUT.ORDER.APPROVED':
                $orderId = $request->resource['id'];

                $amount = $request->resource['purchase_units'][0]['amount']['value'];

                $order = $this->makeApi()->captureOrder($orderId);

                if (@$order['status'] === 'COMPLETED') {

                    $this->subscriptionManager->activateSubscription($subscription);

                    $this->createTransaction(
                        remote_transaction_id: $request->resource['id'],
                        subscription_id: $subscription_id,
                        amount: $amount,
                        currency: $this->currencyCode(),
                        status: Transaction::STATUS_SUCCESS
                    );

                    $this->logInfo('PayPal order captured successfully. ' . $orderId);
                } else {
                    $this->logErrorf('Cannot capture paypal order %s', json_encode($order, JSON_PRETTY_PRINT));
                }

                break;
            case 'PAYMENT.SALE.COMPLETED':
                $this->createTransaction(
                    remote_transaction_id: $request->resource['id'],
                    subscription_id: $subscription_id,
                    amount: $request->resource['amount']['total'],
                    currency: $request->resource['amount']['currency'],
                    status: Transaction::STATUS_SUCCESS
                );

                $this->subscriptionManager->activateSubscription($subscription);

                break;
            case 'PAYMENT.SALE.DENIED':
                $this->createTransaction(
                    remote_transaction_id: $request->resource['id'],
                    subscription_id: $request->resource['custom'],
                    amount: $request->resource['amount']['total'],
                    currency: $request->resource['amount']['currency'],
                    status: Transaction::STATUS_FAILED
                );
            default:
                # code...
                break;
        }
    }

    public function yearlyInterval(): string
    {
        return 'YEAR';
    }

    public function monthlyInterval(): string
    {
        return 'MONTH';
    }

    protected function remoteTransactionIdMetaKey()
    {
        return 'payment';
    }

    protected function makeLifeTimePayLink(Subscription $subscription)
    {
        $this->logDebugf(
            'Creating life time payment link for $%s',
            $subscription->subscription_plan
        );

        $response = $this->makeApi()->createOrder(
            $subscription->subscription_plan->price,
            $this->currencyManager->enabledCurrency()->currency_code,
            $subscription->subscription_plan->description,
            $subscription->id,
            $this->successUrl(),
            $this->canceledUrl(),
            config('app.name')
        );

        $this->logDebugf(
            'Got PayPal response, %s',
            json_encode($response, JSON_PRETTY_PRINT)
        );

        $links = collect($response['links']);

        $link = $links->first(fn($l) => $l['rel'] == 'payer-action') ?? [];

        $link = @$link['href'];

        if (empty($link)) {
            $this->logWarning('Expected paypal link, but got %s', $response);
        }

        return $link;
    }

    protected function makePayLink(Subscription $subscription)
    {
        if ($subscription->subscription_plan->isLifetime()) {
            return $this->makeLifeTimePayLink($subscription);
        }

        $plan_id = $this->getPlanId($subscription->subscription_plan);

        if (!$plan_id) {

            $this->logError(
                'PayPal plan id not found for plan: ' . $subscription->subscription_plan->name . ' (' . $subscription->subscription_plan->id . ')'
            );

            return null;
        }

        $paypal_subscription = $this
            ->api()
            ->createSubscription(
                $plan_id,
                $subscription->id,
                $subscription->user->email,
                config('app.name'),
                $this->successUrl(),
                $this->canceledUrl()
            );

        $link =  @$this->api()
            ->getLink($paypal_subscription['links'], 'approve');

        if (!$link) {
            $this->logErrorf(
                'Expected PayPal response with approve link, but got instead %s',
                json_encode(
                    $paypal_subscription,
                    JSON_PRETTY_PRINT
                )
            );
        }

        return $link;
    }

    protected function syncSubscriptionPlan(SubscriptionPlan $plan): string
    {
        if ($plan->isLifetime()) {
            return '';
        }

        $product = $this->api()->createProduct(
            name: $plan->name,
            description: $plan->description
        );

        $paypal_plan = $this->api()->createSubscriptionPlan(
            paypal_product_id: $product['id'],
            name: $plan->name,
            description: $plan->description,
            interval: $this->resolveInterval($plan),
            price: $plan->price,
            currency_code: $this->currencyManager->enabledCurrency()->currency_code
        );

        if (!isset($paypal_plan['id'])) {
            Log::error('Expected paypal plan with valid id.', compact('paypal_plan'));
            return '';
        }

        $this->setPlanId($plan, $paypal_plan['id']);

        return $this->getPlanId($plan);
    }

    protected function fetchRemoteSubscriptionFromReturnUrl($queryParams)
    {
        return $this->api()->getSubscription(@$queryParams['subscription_id']);
    }

    protected function isRemoteSubscriptionActive($remote_subscription): bool
    {
        return @$remote_subscription['status'] === 'ACTIVE';
    }

    protected function resolveLocalSubscription($remote_subscription): ?Subscription
    {
        return Subscription::find(@$remote_subscription['custom_id']);
    }

    protected function setPlanId(SubscriptionPlan $plan, $id)
    {
        $plan->setMeta($this->slug() . '_plan_id', $id);
    }

    protected function getPlanId(SubscriptionPlan $subscriptionPlan)
    {
        return $subscriptionPlan->getMeta($this->slug() . '_plan_id');
    }

    public function registerWebhook(): bool
    {
        try {
            $webhooks = $this->api()->listWebhooks();

            $webhooks = collect($webhooks);

            $webhook = $webhooks->first(fn($w) => $w['url'] == $this->webhookUrl());

            if ($webhook) {
                return true;
            }

            $webhook = $this->api()->registerWebhook($this->webhookUrl());

            if (!@$webhook['id']) {
                throw new Exception('Expected webhook response with valid id. Got ' . json_encode($webhook));
            }

            return true;
        } catch (Exception $ex) {
            Log::error('Cannot register webhook. ' . $ex->getMessage());
            return false;
        }
    }

    protected function verifyWebhook(Request $request): bool
    {
        return true;
    }
}
