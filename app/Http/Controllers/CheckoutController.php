<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Support\PaymentProcessors\PaymentProcessorManager;
use App\Support\PaymentProcessors\Interfaces\ActivatesSubscriptionOnReturnUrl;
use App\Support\PaymentProcessors\PaymentProcessor;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    private PaymentProcessorManager $paymentProcessorManager;

    public function __construct(PaymentProcessorManager $paymentProcessorManager)
    {
        $this->paymentProcessorManager = $paymentProcessorManager;
    }

    public function paymentSuccess(Request $request)
    {
        $processor = $this->paymentProcessorManager->getBySlug($request->payment_gateway);

        if (!$processor) {
            return redirect()->to(route('payment.invalid'));
        }

        if ($processor instanceof ActivatesSubscriptionOnReturnUrl) {

            try {

                $processor->verifyReturnUrlQueryParams($request->all());

                return $this->redirectToThankyouRoute($processor);
                //
            } catch (Exception $ex) {

                return redirect()->to(route('payment.invalid'));
            }
        } else {
            return $this->redirectToThankyouRoute($processor);
        }
    }

    private function redirectToThankyouRoute(PaymentProcessor $processor)
    {
        return redirect()->to(route('payment.thankyou', [
            'processor' => $processor->slug()
        ]));
    }

    public function paymentThankyou()
    {
        return view('payment.thankyou');
    }

    public function paymentInvalid()
    {
        return view('payment.invalid');
    }

    public function paymentCanceled()
    {
        return view('payment.canceled');
    }
}
