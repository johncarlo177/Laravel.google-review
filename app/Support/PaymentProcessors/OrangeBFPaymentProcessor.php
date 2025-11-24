<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Rules\MobileNumberRule;
use App\Support\PaymentProcessors\Api\OrangeBF;
use App\Support\PaymentProcessors\Api\PayTr;
use App\Support\PaymentProcessors\Interfaces\ForwardsApiCalls;
use App\Support\PaymentProcessors\Interfaces\SelfHostedPaymentProcessor;
use App\Support\PaymentProcessors\Traits\RendersSelfHostedRoutes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class OrangeBFPaymentProcessor extends PaymentProcessor implements SelfHostedPaymentProcessor, ForwardsApiCalls
{
    use RendersSelfHostedRoutes;

    public function slug()
    {
        return 'orange-bf';
    }

    public function api()
    {
        return new OrangeBF(
            mode: $this->config('mode'),
            merchant: $this->config('merchant'),
            username: $this->config('login_id'),
            password: $this->config('password')
        );
    }

    protected function doTestCredentials(): bool
    {
        return false;
    }

    protected function verifyWebhook(Request $request): bool
    {
        return false;
    }

    protected function handleVerifiedWebhook(Request $request)
    {
    }

    protected function remoteTransactionIdMetaKey()
    {
        return '';
    }

    public function forwardedVerifyOtp($data)
    {
        $validator = Validator::make($data, [
            'mobile_number' => [
                'required', new MobileNumberRule()
            ],
            'otp' => ['required'],
            'subscriptionId' => ['required', Rule::in(Subscription::pluck('id'))],
        ]);

        $validator->validate();

        /** @var Subscription */
        $subscription = Subscription::findOrFail($data['subscriptionId']);

        $amount = $this->price($subscription);

        $mobileNumber = $data['mobile_number']['mobile_number'];

        Log::debug('Verifying OTP for mobile number ' . $mobileNumber);

        $response = [];

        try {
            $response = $this->api()->verifyPayment(
                mobileNumber: $mobileNumber,
                otp: $data['otp'],
                amount: $amount,
                referenceNumber: "subscription-" . $subscription->id,
                extTransactionId: "subscription-" . $subscription->id,
            );

            Log::debug("Orange Response = " . json_encode($response, JSON_PRETTY_PRINT));
        } catch (Throwable $th) {
            Log::error('Orange API Error');
            Log::error($th->getMessage());
        }

        if (empty($response['status'])) {
            Log::error('Expected Orange response with status key, got ' . json_encode($response, JSON_PRETTY_PRINT));
        }

        if (@$response['status'] == 200) {
            $this->subscriptionManager->activateSubscription($subscription);

            $transaction = $this->createTransaction(
                remote_transaction_id: $response['transID'],
                subscription_id: $subscription->id,
                amount: $amount,
                currency: $this->currencyManager->enabledCurrency()->currency_code,
                status: Transaction::STATUS_SUCCESS
            );

            return [
                'success' => true,
                'transaction_id' => $transaction->id,
                'subscription_id' => $subscription->id,
                'plan_name' => $subscription->subscription_plan->name,
            ];
        }

        $resultValidator = Validator::make([], []);

        $resultValidator->after(function ($v) use ($response) {
            $message = @$response['message'] ?? t('Invalid OTP number');

            $v->errors()->add('otp', $message);
        });

        $resultValidator->validate();

        return [
            'success' => false
        ];
    }
}
