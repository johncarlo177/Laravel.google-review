<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Support\PaymentProcessors\Interfaces\ForwardsApiCalls;
use App\Support\PaymentProcessors\Interfaces\SelfHostedPaymentProcessor as InterfacesSelfHostedPaymentProcessor;
use App\Support\PaymentProcessors\Traits\RendersSelfHostedRoutes;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OfflinePaymentProcessor extends PaymentProcessor
implements InterfacesSelfHostedPaymentProcessor, ForwardsApiCalls
{
    use RendersSelfHostedRoutes;

    public function slug()
    {
        return 'offline-payment';
    }

    protected function verifyWebhook(Request $request): bool
    {
        return false;
    }

    protected function handleVerifiedWebhook(Request $request)
    {
    }

    protected function doTestCredentials(): bool
    {
        return false;
    }

    public function registerWebhook(): bool
    {
        return false;
    }

    public function clientFields()
    {
        return [
            'customer_instructions' => Str::markdown($this->config('customer_instructions')),
        ];
    }

    public function forwardedIsPaymentProofDisabled()
    {
        return [
            'result' => $this->isPaymentProofDisabled()
        ];
    }

    private function isPaymentProofDisabled()
    {
        return $this->config('payment_proof') === 'disabled';
    }

    public function forwardedSkipFileUpload()
    {
        if (!$this->isPaymentProofDisabled()) {
            return [
                'result' => false
            ];
        }

        $this->subscriptionManager->activateSubscription(
            Subscription::findOrFail(
                request()->subscription_id
            )
        );

        return ['result' => true];
    }
}
