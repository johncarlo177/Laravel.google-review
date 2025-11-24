<?php

namespace App\Http\Controllers;

use App\Support\PaymentProcessors\StripePaymentProcessor;

use Illuminate\Http\Request;


class StripeWebhook extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $stripe = new StripePaymentProcessor();

        return $stripe->receiveWebhook($request);
    }
}
