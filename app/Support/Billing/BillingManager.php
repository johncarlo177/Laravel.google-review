<?php

namespace App\Support\Billing;

use App\Models\Config;

class BillingManager
{
    public function isAccountCreditBilling()
    {
        $value = Config::get('billing.mode');

        return $value === 'account_credit';
    }

    public function isSubscriptionBilling()
    {
        return !$this->isAccountCreditBilling();
    }
}
