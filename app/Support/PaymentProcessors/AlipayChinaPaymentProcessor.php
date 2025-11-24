<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Api\AlipayChina;
use App\Support\PaymentProcessors\Interfaces\SelfHostedPaymentProcessor;
use App\Support\PaymentProcessors\Traits\RendersSelfHostedRoutes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AlipayChinaPaymentProcessor extends PaymentProcessor implements SelfHostedPaymentProcessor
{
    use RendersSelfHostedRoutes;

    public function slug()
    {
        return 'alipay-china';
    }

    public function api()
    {
        return new AlipayChina(
            app_id: $this->config('app_id'),
            app_secret_cert: $this->config('app_secret_cert'),
            app_public_cert_path: $this->configFilePath(
                'app_public_cert'
            ),
            alipay_public_cert_path: $this->configFilePath(
                'alipay_public_cert'
            ),
            alipay_root_cert_path: $this->configFilePath(
                'alipay_root_cert'
            ),
            return_url: $this->successUrl(),
            notify_url: $this->webhookUrl(),
            app_auth_token: $this->config('app_auth_token'),
            mode: $this->config('mode')
        );
    }
    /**
     * Alipay returns html page with script tag that automatically submits the page.
     * See this comment: github.com/yansongda/pay/issues/729#issuecomment-1365092539
     */
    public function getAliPayHtmlForm(Subscription $subscription)
    {
        $result = $this->api()->createOrder(
            out_trade_no: $subscription->id,
            total_amount: $this->price($subscription),
            subject: $this->planDescription($subscription)
        );

        return $result;
    }

    protected function doTestCredentials(): bool
    {
        return true;
    }

    protected function verifyWebhook(Request $request): bool
    {
        Log::debug(json_encode($request->all(), JSON_PRETTY_PRINT));

        $data = $this->api()->getNotifyCallbackData();

        return !empty($data);
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $data = $this->api()->getNotifyCallbackData();

        $subscription_id = $data['out_trade_no'];

        $subscription = Subscription::find($subscription_id);

        $this->subscriptionManager->activateSubscription($subscription);

        $this->createTransaction(
            remote_transaction_id: $data->trade_no,
            subscription_id: $subscription_id,
            amount: $data->total_amount,
            currency: 'CNY',
            status: Transaction::STATUS_SUCCESS
        );
    }
}
