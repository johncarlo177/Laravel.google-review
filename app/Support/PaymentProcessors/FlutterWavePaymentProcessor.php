<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Interfaces\SelfHostedPaymentProcessor;
use App\Support\PaymentProcessors\Traits\RendersSelfHostedRoutes;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;
use Throwable;

class FlutterWavePaymentProcessor extends PaymentProcessor implements SelfHostedPaymentProcessor
{
    use RendersSelfHostedRoutes;
    use WriteLogs;

    public function slug()
    {
        return 'flutterwave';
    }

    public function api() {}

    protected function verifyWebhook(Request $request): bool
    {
        return $request->input('event') === 'charge.completed';
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $data = $request->input('data');

        $reference = $data['tx_ref'];

        $subscription_id = str_replace('subscription-', '', $reference);

        $this->subscriptionManager->activateSubscription(
            Subscription::find($subscription_id)
        );

        $this->createTransaction(
            remote_transaction_id: $data['id'],
            subscription_id: $subscription_id,
            amount: $data['amount'],
            currency: $data['currency'],
            status: Transaction::STATUS_SUCCESS
        );
    }

    protected function doTestCredentials(): bool
    {
        return false;
    }

    public function publicKey()
    {
        return $this->config('public_key');
    }

    public function transactionRef($subscription)
    {
        return sprintf('subscription-%s', $subscription->id);
    }
}
