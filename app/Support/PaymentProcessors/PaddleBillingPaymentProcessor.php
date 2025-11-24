<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Models\User;
use App\Support\PaymentProcessors\Api\PaddleBilling;
use App\Support\PaymentProcessors\Interfaces\CancelsSubscription;
use App\Support\PaymentProcessors\Interfaces\CanSyncSubscriptionPlans;
use App\Support\PaymentProcessors\Interfaces\ChangesSubscription;
use App\Support\PaymentProcessors\Interfaces\RegistersWebhook;
use App\Support\PaymentProcessors\Interfaces\SelfHostedPaymentProcessor;
use App\Support\PaymentProcessors\Traits\HandleChangeSubscription;
use App\Support\PaymentProcessors\Traits\HandlesSyncSubscriptionPlans;
use App\Support\PaymentProcessors\Traits\RendersSelfHostedRoutes;
use App\Support\System\Traits\WriteLogs;
use Exception;
use Illuminate\Http\Request;
use Throwable;

class PaddleBillingPaymentProcessor extends PaymentProcessor implements
    CanSyncSubscriptionPlans,
    CancelsSubscription,
    ChangesSubscription,
    SelfHostedPaymentProcessor,

    RegistersWebhook
{
    use HandleChangeSubscription;
    use RendersSelfHostedRoutes;
    use HandlesSyncSubscriptionPlans;
    use WriteLogs;

    public function slug()
    {
        return 'paddle-billing';
    }

    public static function api()
    {
        $instance = new static;

        return PaddleBilling::instance(
            apiKey: $instance->config('api_key'),
            isSandbox: $instance->config('mode') != 'live'
        );
    }

    protected function syncSubscriptionPlan(SubscriptionPlan $plan): string
    {
        $product = $this->api()->createProduct(
            name: $plan->name
        )->json();

        if ($plan->isLifetime()) {
            $price = $this->api()->createOneTimePrice(
                description: $plan->description,
                product_id: $product['data']['id'],
                amount: $plan->price,
                currency: $this->currencyCode(),
            )->json();
        } else {
            $price = $this->api()->createPrice(
                description: $plan->description,
                product_id: $product['data']['id'],
                amount: $plan->price,
                currency: $this->currencyCode(),
                interval: $this->resolveInterval($plan),
                frequency: 1
            )->json();
        }

        $id = $price['data']['id'];

        $this->setPriceId($plan, $id);

        return $id;
    }

    public function getPriceId(SubscriptionPlan $plan)
    {
        return $plan->getMeta($this->metaKey('price_id'));
    }

    private function setPriceId(SubscriptionPlan $plan, $id)
    {
        $plan->setMeta($this->metaKey('price_id'), $id);
    }

    private function metaKey($key)
    {
        return sprintf('%s_%s', static::class, $key);
    }

    public function createApiPlan($id)
    {
        $plan = SubscriptionPlan::find($id);

        return $this->syncSubscriptionPlan($plan);
    }

    public function yearlyInterval(): string
    {
        return PaddleBilling::INTERVAL_YEARLY;
    }

    public function monthlyInterval(): string
    {
        return PaddleBilling::INTERVAL_MONTHLY;
    }

    protected function verifyWebhook(Request $request): bool
    {
        return true;
    }

    /**
     * @return Subscription
     */
    protected function resolveLocalSubscription($webhookData)
    {
        // For one time payment, custom_data is found in the data array directly 
        // without fetching the remote subscription

        $local_subscription_id = null;

        /**
         * @var Subscription
         */
        $local_subscription = null;

        $remote_subscription_id = @$webhookData['subscription_id'];

        try {
            $local_subscription_id = @$webhookData['custom_data']['subscription_id'];

            $local_subscription = Subscription::find($local_subscription_id);
        } catch (Throwable $th) {
            // 
        }

        if (empty($local_subscription)) {
            // Fetch remote subscription

            $remote_subscription = @$this->api()->getSubscription($remote_subscription_id)->json()['data'];

            if (!$remote_subscription) {
                $this->logWarning(
                    'Error in webhook: expected subscription with data key. subscription = %s',
                    json_encode($remote_subscription, JSON_PRETTY_PRINT),

                );

                return;
            }

            $local_subscription_id = @$remote_subscription['custom_data']['subscription_id'];

            /**
             * @var Subscription
             */
            $local_subscription = Subscription::find($local_subscription_id);
        }

        $this->setRemoteSubscriptionId(
            $local_subscription->user,
            $remote_subscription_id
        );

        $this->logDebug(
            'Setting paddle subscription id. Subscription (%s), paddle id (%s)',
            $local_subscription->id,
            $remote_subscription_id
        );

        return $local_subscription;
    }

    protected function changeSubscription($remoteId, SubscriptionPlan $toPlan)
    {
        $priceId = $this->getPriceId($toPlan);

        $response = $this->api()->updateSubscription($remoteId, $priceId);

        $data = $response->json('data');

        if (!is_array($data)) {
            $this->logDebug(
                'Expected Paddle response with data. But got instead: %s',
                response()->json()
            );
        }

        $data = $this->api()->updateSubscription($remoteId, $priceId)->json('data');

        if (!is_array($data) && @$data['status'] !== 'active') {
            throw new Exception('Subscription is not active');
        }
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $transaction = $request->input('data');

        $this->logDebug('Wehbook transaction = %s', $transaction);

        $localSubscription = $this->resolveLocalSubscription($transaction);

        if (!$localSubscription) {
            $this->logWarning(
                'Error in webhook: cannot resolve local subscription. Webhook data = %s',
                $request->all()
            );

            return;
        }

        $this->subscriptionManager->activateSubscription($localSubscription);

        $this->createTransaction(
            remote_transaction_id: $transaction['id'],
            subscription_id: $localSubscription->id,
            amount: $transaction['details']['totals']['total'] / 100,
            currency: $transaction['currency_code'],
            status: Transaction::STATUS_SUCCESS
        );

        return 'OK';
    }

    public function cancelRemoteSubscription(Subscription $subscription)
    {
        $subscription_id = $this->getRemoteSubscriptionId($subscription->user);

        $this->logDebug('Remote subscription id = %s', $subscription_id);

        $this->api()->cancelSubscription($subscription_id);
    }

    protected function doTestCredentials(): bool
    {
        return $this->api()
            ->listWebhookTypes()
            ->success();
    }

    public function getMode()
    {
        return $this->config('mode');
    }

    public function getClientSideToken()
    {
        return $this->config('client_token');
    }

    public function registerWebhook(): bool
    {
        if (!$this->api()->isNotificationRegistered($this->webhookUrl())) {
            $this->api()->createNotificationSettings(
                'Quick Code Webhook',
                $this->webhookUrl(),
                subscribed_events: [
                    PaddleBilling::EVENT_TRANSACTION_COMPLETED,
                ]
            );
        }

        return $this->api()
            ->isNotificationRegistered(
                $this->webhookUrl()
            );
    }
}
