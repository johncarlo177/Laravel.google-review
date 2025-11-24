<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use App\Support\PaymentProcessors\Api\Xendit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditPaymentProcessor extends PaymentProcessor
{
    public function slug()
    {
        return 'xendit';
    }

    private function api()
    {
        return new Xendit($this->config('public_key'), $this->config('secret_key'));
    }

    protected function doTestCredentials(): bool
    {
        $invoice = $this->api()->createInvoice(
            external_id: 'test_invoice_' . time(),
            amount: 1000,
            description: 'Test invoice created by the API to confirm credentials are working',
            payer_email: request()->user()->email,
            success_redirect_url: $this->successUrl(),
            failure_redirect_url: $this->canceledUrl()
        );

        if (empty($invoice['id'])) {
            Log::debug('Xendit: Expected invoice with ID while testing credentials, got ' . json_encode($invoice, JSON_PRETTY_PRINT));
            return false;
        }

        return true;
    }

    protected function makePayLink(Subscription $subscription)
    {
        $invoice = $this->api()->createInvoice(
            external_id: json_encode(['subscription_id' => $subscription->id]),
            amount: $this->price($subscription),
            description: $this->planDescription($subscription),
            payer_email: $subscription->user->email,
            success_redirect_url: $this->successUrl(),
            failure_redirect_url: $this->canceledUrl()
        );

        if (empty($invoice['invoice_url'])) {
            Log::error(
                'Expected Xendit response with invoice_url, got' . json_encode(
                    $invoice,
                    JSON_PRETTY_PRINT
                )
            );
            return null;
        }

        return $invoice['invoice_url'];
    }

    protected function verifyWebhook(Request $request): bool
    {
        $id = $request->id;

        if (empty($id)) {
            Log::error('id is empty');
            return false;
        }

        $invoice = $this->api()->getInvoice($id);

        if (empty($invoice['id'])) {
            Log::error('Cannot get Xendit invoice. Got ' . json_encode($invoice, JSON_PRETTY_PRINT));
            return false;
        }

        $subscription_id = @json_decode($invoice['external_id'], true)['subscription_id'];

        if (empty($subscription_id)) {
            Log::error('Cannot get subscription id, got ' . json_encode($invoice, JSON_PRETTY_PRINT));
            return false;
        }

        $subscription = Subscription::find($subscription_id);

        if (empty($subscription)) {
            Log::error('Cannot cannot find local subscription.');

            return false;
        }

        return true;
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $invoice = $this->api()->getInvoice($request->id);

        $subscription_id = json_decode($invoice['external_id'], true)['subscription_id'];

        $subscription = Subscription::find($subscription_id);

        $this->subscriptionManager->activateSubscription($subscription);

        $this->createTransaction(
            remote_transaction_id: $invoice['id'],
            subscription_id: $subscription_id,
            amount: $invoice['amount'],
            currency: $invoice['currency'],
            status: Transaction::STATUS_SUCCESS
        );
    }
}
