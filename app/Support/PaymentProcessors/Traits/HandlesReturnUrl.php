<?php

namespace App\Support\PaymentProcessors\Traits;

use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

trait HandlesReturnUrl
{
    private function shouldHandleReturnUrl()
    {
        return true;
    }

    public function verifyReturnUrlQueryParams($queryParams)
    {
        if (!$this->shouldHandleReturnUrl()) {
            return true;
        }

        Log::debug($this->slug() . ': Verifying return URL. ');
        Log::debug($this->slug() . ': Query Params:  ' . json_encode($queryParams));

        $remote_subscription = $this->fetchRemoteSubscriptionFromReturnUrl($queryParams);

        if (!$this->isRemoteSubscriptionActive($remote_subscription)) {

            Log::debug($this->slug() . ': invalid remote subscription ' . var_export($remote_subscription, true));

            throw new InvalidArgumentException(t('Invalid return url params.'));
        }

        $subscription = $this->resolveLocalSubscription($remote_subscription);

        if ($subscription) {
            $this->subscriptionManager->activateSubscription($subscription);

            Log::debug($this->slug() . ': local subscription activated ');
        } {
            Log::debug($this->slug() . ': local subscription not found ');
        }
    }

    protected abstract function fetchRemoteSubscriptionFromReturnUrl($queryParams);

    protected abstract function isRemoteSubscriptionActive($remote_subscription): bool;

    protected abstract function resolveLocalSubscription($remote_subscription): ?Subscription;

    public abstract function slug();
}
