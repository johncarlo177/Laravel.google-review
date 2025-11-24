<?php

namespace App\Support\PaymentProcessors\Traits;

trait HandlesAccountCreditBilling
{
    private function accountCreditOrderDescription()
    {
        return t('Account balance recharge.');
    }
}
