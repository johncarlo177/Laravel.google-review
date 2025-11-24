<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Api\Paddle;
use App\Support\PaymentProcessors\Interfaces\ActivatesSubscriptionOnReturnUrl;
use App\Support\PaymentProcessors\Interfaces\ForwardsApiCalls;
use App\Support\PaymentProcessors\Interfaces\SelfHostedPaymentProcessor as InterfacesSelfHostedPaymentProcessor;
use App\Support\PaymentProcessors\Traits\HandlesReturnUrl;
use App\Support\PaymentProcessors\Traits\RendersSelfHostedRoutes;
use Illuminate\Http\Request;


class PaddlePaymentProcessor extends PaymentProcessor implements
    InterfacesSelfHostedPaymentProcessor,
    ActivatesSubscriptionOnReturnUrl,
    ForwardsApiCalls
{
    use HandlesReturnUrl;
    use RendersSelfHostedRoutes;

    private Paddle $api;

    public function __construct()
    {
        parent::__construct();

        $this->api = new Paddle(
            $this->config('mode'),
            $this->config('vendor_id'),
            $this->config('auth_code')
        );
    }

    public function slug()
    {
        return 'paddle';
    }

    protected function fetchRemoteSubscriptionFromReturnUrl($queryParams)
    {
        return $this->api->getCheckoutObject(@$queryParams['checkout_id']);
    }

    protected function isRemoteSubscriptionActive($remote_subscription): bool
    {
        return !empty(@$remote_subscription['order']['order_id']);
    }

    protected function resolveLocalSubscription($remote_subscription): ?Subscription
    {
        return Subscription::find(@$remote_subscription['custom_data']['subscription_id']);
    }

    protected function verifyWebhook(Request $request): bool
    {
        // Your Paddle 'Public Key'
        $public_key_string = $this->config('public_key');

        $public_key = openssl_get_publickey($public_key_string);

        // Get the p_signature parameter & base64 decode it.
        $signature = base64_decode($_POST['p_signature']);

        // Get the fields sent in the request, and remove the p_signature parameter
        $fields = $_POST;

        unset($fields['p_signature']);

        // ksort() and serialize the fields
        ksort($fields);

        foreach ($fields as $k => $v) {
            if (!in_array(gettype($v), array('object', 'array'))) {
                $fields[$k] = "$v";
            }
        }

        $data = serialize($fields);

        // Verify the signature
        $verification = openssl_verify($data, $signature, $public_key, OPENSSL_ALGO_SHA1);

        return $verification === 1;
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        if ($request->alert_name == 'subscription_payment_succeeded') {
            $this->handleSubscriptionPaymentSuccessWebhook($request);
        }

        if ($request->alert_name == 'subscription_payment_failed') {
            $this->handleSubscriptionPaymentFailedWebhook($request);
        }
    }

    protected function remoteTransactionIdMetaKey()
    {
        return 'subscription_payment_id';
    }

    private function handleSubscriptionPaymentFailedWebhook(Request $request)
    {
        $subscription = $this->getSubscriptionFromWebhookRequest($request);

        $this->createTransaction(
            $request->subscription_payment_id,
            $subscription->id,
            $request->earnings,
            $request->currency,
            Transaction::STATUS_FAILED
        );
    }

    private function handleSubscriptionPaymentSuccessWebhook(Request $request)
    {
        $subscription = $this->getSubscriptionFromWebhookRequest($request);

        $this->subscriptionManager->activateSubscription($subscription);

        $this->createTransaction(
            $request->subscription_payment_id,
            $subscription->id,
            $request->earnings,
            $request->currency,
            Transaction::STATUS_SUCCESS
        );
    }

    private function getSubscriptionFromWebhookRequest(Request $request): ?Subscription
    {
        $data = json_decode($request->custom_data);

        return Subscription::find(@$data->subscription_id);
    }

    protected function doTestCredentials(): bool
    {
        if (!$this->api->testCredentias()) {
            return false;
        }

        return
            $this->verifyAllPaddleIdsNotEmpty() && $this->verifyAllPlansExistInPaddle();
    }

    private function savePaddlePlanName(SubscriptionPlan $plan, $name)
    {
        $this->setConfig(
            $this->paddleNameConfigKey($plan->id),
            $name
        );
    }

    public function forwardedListSubscriptionPlans()
    {
        return $this->api->listSubscriptionPlans();
    }

    private function verifyAllPlansExistInPaddle()
    {
        $subscriptionPlans = $this->getPaddlePlanIds(returnSubscriptionPlans: true);

        $paddlePlans = collect(@$this->api->listSubscriptionPlans());

        $allFoundInPaddle = $subscriptionPlans->reduce(
            function (
                $result,
                $subscriptionPlan
            ) use ($paddlePlans) {

                $found = $paddlePlans->first(
                    fn (
                        $paddlePlan
                    ) => $paddlePlan['id'] == $subscriptionPlan->paddle_id
                );

                if ($found) {
                    $this->savePaddlePlanName($subscriptionPlan, $found['name']);
                }

                if (!$found && !empty($subscriptionPlan->paddle_id)) {
                    $this->testCredentialMessages[] = sprintf('%s Plan ID (%s) not found.', $subscriptionPlan->name, $subscriptionPlan->paddle_id);
                }

                return $result && !!$found;
            },
            true
        );

        if (!$allFoundInPaddle) {
            return false;
        }

        return true;
    }

    private function verifyAllPaddleIdsNotEmpty()
    {
        $paddlePlanIds = $this->getPaddlePlanIds();

        $emptyPaddleIds = $paddlePlanIds->filter(function ($id) {
            return empty($id);
        });

        if ($emptyPaddleIds->isNotEmpty()) {

            $this->testCredentialMessages[] = t('You must fill all Paddle IDs.');

            return false;
        }

        return true;
    }

    public function getPaddleId(SubscriptionPlan $plan)
    {
        return $this->config($this->paddleIdConfigKey($plan->id));
    }

    private function getPaddlePlanIds($returnSubscriptionPlans = false)
    {
        $plans = SubscriptionPlan::where('is_trial', false)->get();

        if ($returnSubscriptionPlans) {
            return $plans->map(function ($plan) {
                $plan->paddle_id = $this->config(
                    $this->paddleIdConfigKey($plan->id)
                );

                return $plan;
            });
        }

        return $plans->map(
            fn ($plan) => $this->config(
                $this->paddleIdConfigKey($plan->id)
            )
        );
    }

    private function paddleNameConfigKey($planId)
    {
        return 'paddle_name_for_plan_' . $planId;
    }

    private function paddleIdConfigKey($planId)
    {
        return 'paddle_id_for_plan_' . $planId;
    }

    public function getVendorId()
    {
        return $this->config('vendor_id');
    }

    public function getMode()
    {
        return $this->config('mode');
    }
}
