<?php

namespace App\Support\PaymentProcessors\Api;

use Illuminate\Support\Facades\Http;

class Paddle
{
    private $auth_code;
    private $mode;
    private $vendor_id;

    public function __construct($mode, $vendor_id, $auth_code)
    {
        $this->mode = $mode;
        $this->vendor_id = $vendor_id;
        $this->auth_code = $auth_code;
    }

    private function doListSubscriptionPlans()
    {
        $url = 'vendors.paddle.com/api/2.0/subscription/plans';

        return $this->protectedPost($url)->json();
    }

    public function listSubscriptionPlans()
    {
        return $this->doListSubscriptionPlans()['response'];
    }

    public function testCredentias()
    {
        return !!@$this->doListSubscriptionPlans()['success'];
    }

    public function getCheckoutObject($checkoutId)
    {
        $url = 'checkout.paddle.com/api/1.0/order/?checkout_id=' . $checkoutId;

        return $this->get($url)->json();
    }

    private function get($url)
    {
        return Http::get($this->url($url));
    }

    private function protectedPost($url)
    {
        return Http::asForm()->post($this->url($url), [
            'vendor_id' => $this->vendor_id,
            'vendor_auth_code' => $this->auth_code
        ]);
    }

    private function post($url)
    {
    }

    private function url($url)
    {
        if ($this->mode === 'sandbox') {
            return "https://sandbox-$url";
        }

        return "https://$url";
    }
}
