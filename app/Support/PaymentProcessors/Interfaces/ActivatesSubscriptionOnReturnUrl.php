<?php

namespace App\Support\PaymentProcessors\Interfaces;

interface ActivatesSubscriptionOnReturnUrl
{
    public function verifyReturnUrlQueryParams($queryParams);
}
