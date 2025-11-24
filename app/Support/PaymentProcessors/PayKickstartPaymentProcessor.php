<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Traits\GeneratesActiveSubscription;
use App\Support\PaymentProcessors\Traits\MapsPlanFields;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PayKickstartPaymentProcessor extends PaymentProcessor
{
    use MapsPlanFields, GeneratesActiveSubscription;

    public function slug()
    {
        return 'paykickstart';
    }

    protected function doTestCredentials(): bool
    {
        return true;
    }

    private function getCheckoutPageUrl(SubscriptionPlan $plan)
    {
        return $this->getMappedPlanField($plan, 'checkout_page_url');
    }

    private function getPayKickstartProductId(SubscriptionPlan $plan)
    {
        return $this->getMappedPlanField($plan, 'product_id');
    }

    protected function makePayLink(Subscription $subscription)
    {
        return $this->getCheckoutPageUrl(
            $subscription->subscription_plan
        ) . '?subscription_id=' . $subscription->id;
    }

    protected function verifyWebhook(Request $request): bool
    {
        $data = $_POST;

        $secret_key = $this->config('secret_key');

        if (!isset($data['hash']) || !isset($data['verification_code'])) {
            return false;
        }

        // Hash received
        $ipnHash = $data['hash'];

        // Unset encrypted keys
        unset($data['hash'], $data['verification_code']);

        // Trim and ommit empty/null values from the data
        $data = array_filter(array_map('trim', $data));

        // Alphabetically sort IPN parameters by their key. This ensures
        // the params are in the same order as when Paykickstart
        // generated the verification code, in order to prevent
        // hash key invalidation due to POST parameter order.
        ksort($data, SORT_STRING);

        // Implode all the values into a string, delimited by "|"
        // Generate the hash using the imploded string and secret key
        $hash = hash_hmac('sha1', implode("|", $data), $secret_key);

        return $hash == $ipnHash;
    }

    protected function createSubscriptionAndSendLoginDetails(Request $request)
    {
        $email = $this->getBuyerEmail($request->all());

        $name = $this->getBuyerName($request->all());

        $plan = $this->getSubscriptionPlanByProductId($request->product_id);

        $result = $this->generateUser(name: $name, email: $email);

        $password = @$result['password'];

        $user = $result['user'];

        $subscription = $this->generateActiveSubscription($user, $plan);

        $transaction = $this->createTransaction(
            remote_transaction_id: $request->transaction_id,
            subscription_id: $subscription->id,
            amount: $request->amount,
            currency: $this->currencyManager->enabledCurrency()->currency_code,
            status: Transaction::STATUS_SUCCESS
        );

        $this->setIPNData($transaction, $request->all());

        $variables = [
            'EMAIL' => $email,
            'FULL_NAME' => $name,
            'PLAN_NAME' => $plan->name,
            'PASSWORD' => $password
        ];

        $template = $password ? $this->config('email_template') : $this->config('upgrade_email_template');

        foreach ($variables as $key => $value) {
            if (!$value) continue;

            $template = str_replace($key, $value, $template);
        }

        Mail::raw($template, function ($message) use ($email, $name) {
            $message->subject('Subscription Details - ' . config('app.name'));
            $message->to($email, $name);
        });
    }

    private function getSubscriptionPlanByProductId($productId)
    {
        $plans = SubscriptionPlan::where('is_hidden', false)->where('is_trial', false)->get();

        return $plans->first(
            fn ($plan) => $this->getPayKickstartProductId($plan) == $productId
        );;
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        if ($request->event != 'subscription-payment') return;

        $subscription_id = $request->custom_subscription_id;

        if (empty($subscription_id)) {
            return $this->createSubscriptionAndSendLoginDetails($request);
        }

        $transaction = $this->createTransaction(
            remote_transaction_id: $request->transaction_id,
            subscription_id: $subscription_id,
            amount: $request->amount,
            currency: $this->currencyManager->enabledCurrency()->currency_code,
            status: Transaction::STATUS_SUCCESS
        );

        $this->setIPNData($transaction, $request->all());

        $this->subscriptionManager->activateSubscription(Subscription::find($subscription_id));
    }

    private function getBuyerName($ipnData)
    {
        return sprintf('%s %s', $ipnData['buyer_first_name'], $ipnData['buyer_last_name']);
    }

    private function getBuyerEmail($ipnData)
    {
        return $ipnData['buyer_email'];
    }

    /**
     * Get the data received over instant payment notification of PayKickstart.
     */
    private function getIPNData(Transaction $transaction)
    {
        return json_decode(
            $transaction->getMeta('paykickstart_ipn_data')
        );
    }

    private function setIPNData(Transaction $transaction, $data)
    {
        return $transaction->setMeta('paykickstart_ipn_data', json_encode($data));
    }
}
