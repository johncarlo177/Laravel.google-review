<?php

namespace App\Support\PaymentProcessors\Interfaces;

use App\Models\Subscription;

interface CancelsSubscription
{
    public function cancelRemoteSubscription(Subscription $subscription);
}
