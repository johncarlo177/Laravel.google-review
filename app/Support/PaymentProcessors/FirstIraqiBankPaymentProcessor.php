<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\SubscriptionStatus;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Api\Dintero;
use App\Support\PaymentProcessors\Api\FirstIraqiBank;
use App\Support\PaymentProcessors\Interfaces\ForwardsApiCalls;
use App\Support\PaymentProcessors\Interfaces\SelfHostedPaymentProcessor;
use App\Support\PaymentProcessors\Traits\RendersSelfHostedRoutes;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;

class FirstIraqiBankPaymentProcessor extends PaymentProcessor
implements SelfHostedPaymentProcessor, ForwardsApiCalls
{
    use WriteLogs;
    use RendersSelfHostedRoutes;

    private $paymentResponse;

    public function slug()
    {
        return 'fib';
    }

    protected function api()
    {
        return new FirstIraqiBank(
            client_id: $this->config('client_id'),
            client_secret: $this->config('client_secret'),
            isLive: $this->config('mode') === 'production'
        );
    }

    protected function verifyWebhook(Request $request): bool
    {
        return true;
    }


    public function createPayment(Subscription $subscription)
    {
        $this->paymentResponse = $this->api()->createPayment(
            $subscription->subscription_plan->price,
            $subscription->subscription_plan->name,
            $this->webhookUrl() . '?subscription-id=' . $subscription->id
        );

        return $this->paymentResponse;
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $paymentId = $request->input('id');

        $status = $request->input('status');

        $subscriptionId = $request->input('subscription-id');

        $subscription = Subscription::find($subscriptionId);

        if ($status === 'PAID' && $subscription) {
            $this->subscriptionManager->activateSubscription(
                $subscription
            );

            $this->createTransaction(
                remote_transaction_id: $paymentId,
                subscription_id: $subscription->id,
                amount: $subscription->subscription_plan->price,
                currency: 'IQD',
                status: Transaction::STATUS_SUCCESS
            );
        }
    }

    public function forwardedIsSubscriptionActive()
    {
        $id = request()->input('subscription_id');

        /**
         * @var Subscription
         */
        $subscription = Subscription::find($id);

        $status = $subscription?->statuses()->first();

        $this->logDebug('subscription = %s', json_encode($subscription, JSON_PRETTY_PRINT));

        $active =  $status?->status === SubscriptionStatus::STATUS_ACTIVE;

        return [
            'result' => $active
        ];
    }

    protected function doTestCredentials(): bool
    {
        return !empty($this->api()->generateAccessToken());
    }
}
