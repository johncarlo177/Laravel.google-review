<?php

namespace App\Support\PaymentProcessors\Interfaces;

use App\Models\User;

interface ProcessAccountCreditBilling
{
    public function createChargeLink(User $user, $amount);
}
