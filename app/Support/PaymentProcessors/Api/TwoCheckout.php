<?php

namespace App\Support\PaymentProcessors\Api;


class TwoCheckout
{
    private $testing;

    public function __construct(
        bool $testing
    ) {
        $this->testing = $testing ? 1 : 0;
    }

    public function generateBuyLink($twoCheckoutPlanId, $ref)
    {
        $params = [
            'PRODS' => $twoCheckoutPlanId,
            'REF' => $ref,
        ];

        if ($this->testing) {
            $params['DOTEST'] = 1;
        }

        $params = http_build_query($params);

        return sprintf('%s?%s', $this->checkoutUrl(), $params);
    }

    private function checkoutUrl()
    {
        return 'https://secure.2checkout.com/order/checkout.php';
    }
}
