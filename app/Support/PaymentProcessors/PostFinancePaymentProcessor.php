<?php

namespace App\Support\PaymentProcessors;

use App\Models\Subscription;
use App\Support\PaymentProcessors\Api\PostFinanceApi;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;
use Throwable;

class PostFinancePaymentProcessor extends PaymentProcessor
{
    use WriteLogs;

    public function slug()
    {
        return 'postfinance';
    }

    public function api()
    {
        return new PostFinanceApi(
            spaceId: $this->config('space_id'),
            userId: $this->config('user_id'),
            secret: $this->config('secret')
        );
    }

    protected function makePayLink(Subscription $subscription)
    {
        try {
            $link = $this->api()->createPaymentPageForProduct(
                name: $subscription->subscription_plan->name,
                id: $subscription->id,
                sku: sprintf('subscription-%s', $subscription->id),
                currency: $this->currencyCode(),
                amountIncludingTax: $this->calculateAmountIncludingTax($subscription->subscription_plan->price),
            );

            return $link;
        } catch (Throwable $th) {
            $this->logWarning('Cannot create payment link. %s', $th->getMessage());
        }

        return null;
    }

    protected function verifyWebhook(Request $request): bool
    {
        return true;
    }

    protected function handleVerifiedWebhook(Request $request)
    {
        $this->logInfo('Receiving Post Finance webhook %s', json_encode($request->all(), JSON_PRETTY_PRINT));
    }

    protected function doTestCredentials(): bool
    {
        return false;
    }

    private function getTaxPercentage()
    {
        return $this->config('tax_percentage');
    }

    private function calculateAmountIncludingTax($amount)
    {
        $tax = $this->getTaxPercentage();

        $amount = $amount * $tax / 100 + $amount;

        return $amount;
    }
}
