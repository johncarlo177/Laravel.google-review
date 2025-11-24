<?php

namespace App\Support\PaymentProcessors;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use App\Support\PaymentProcessors\Api\PayTr;
use App\Support\PaymentProcessors\Interfaces\SelfHostedPaymentProcessor;
use App\Support\PaymentProcessors\Traits\RendersSelfHostedRoutes;

class PayTrPaymentProcessor extends PaymentProcessor implements SelfHostedPaymentProcessor
{
    use RendersSelfHostedRoutes;

    public function slug()
    {
        return 'paytr';
    }

    public function api()
    {
        return new PayTr(
            $this->config('merchant_id'),
            $this->config('merchant_salt'),
            $this->config('merchant_key'),
            $this->config('mode') === 'test'
        );
    }

    public function generateIframeLink(Subscription $subscription)
    {
        $user_phone = @$subscription->user->getFormattedMobileNumber();

        $response = $this->api()->createPaymentToken(
            customId: $this->getMerchantOidOfSubscription($subscription),
            userIp: request()->ip(),
            userEmail: $subscription->user->email,
            userName: $subscription->user->name,
            userPhone: $user_phone,
            currency: $this->currencyManager->enabledCurrency()->currency_code,
            productDescription: $this->planDescription($subscription),
            amount: $this->price($subscription),
            successUrl: $this->successUrl(),
            failUrl: $this->canceledUrl()
        );

        $token = @$response['token'];

        if (empty($token)) {
            Log::error('Expected PayTr response with token. ' . json_encode($response, JSON_PRETTY_PRINT));
            return null;
        }

        return 'https://www.paytr.com/odeme/guvenli/' . $token;
    }

    private function getMerchantOidOfSubscription(Subscription $subscription)
    {
        return 'subscription' . $subscription->id;
    }

    private function getSubscriptionIdFromMerchantOid($merchantOid)
    {
        return str_replace('subscription', '', $merchantOid);
    }

    protected function doTestCredentials(): bool
    {
        return false;
    }

    protected function verifyWebhook(Request $request): bool
    {
        Log::debug(json_encode($request->all(), JSON_PRETTY_PRINT));

        return $this->api()->verifyWebhook($_POST);
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        if ($request->input('status') !== 'success') return 'OK';

        $subscription_id = $this->getSubscriptionIdFromMerchantOid($request->merchant_oid);

        $subscription = Subscription::find($subscription_id);

        if (empty($subscription)) {
            Log::error(
                'Could not get subscription, subscription_id = ' . $subscription_id . ', merchant_oid = ' . $request->merchant_oid
            );

            return 'OK';
        }

        $this->subscriptionManager->activateSubscription($subscription);

        $this->createTransaction(
            remote_transaction_id: $request->hash,
            subscription_id: $subscription_id,
            amount: $request->total_amount / 100,
            currency: $this->currencyManager->enabledCurrency()->currency_code,
            status: Transaction::STATUS_SUCCESS
        );

        return 'OK';
    }

    protected function remoteTransactionIdMetaKey()
    {
        return 'hash';
    }
}
