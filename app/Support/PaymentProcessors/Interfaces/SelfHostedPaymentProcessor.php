<?php

namespace App\Support\PaymentProcessors\Interfaces;

use Illuminate\Http\Request;

interface SelfHostedPaymentProcessor
{
    public static function payRoute(): string;

    public function renderPayRoute(Request $request);
}
