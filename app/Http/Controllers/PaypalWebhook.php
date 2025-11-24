<?php

namespace App\Http\Controllers;

use App\Support\PaymentProcessors\PayPalPaymentProcessor;
use Illuminate\Http\Request;

class PaypalWebhook extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $paypal = new PayPalPaymentProcessor();

        return $paypal->receiveWebhook($request);
    }
}
