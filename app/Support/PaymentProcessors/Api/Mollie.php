<?php

namespace App\Support\PaymentProcessors\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Mollie
{
    private $apiKey, $partnerId, $partnerProfile;

    public function __construct($apiKey, $partnerId, $partnerProfile)
    {
        $this->apiKey = $apiKey;
        $this->partnerId = $partnerId;
        $this->partnerProfile = $partnerProfile;
    }

    public function getPaymentMethods()
    {
        return $this->request()->get('methods')->json();
    }

    public function createPayment($amount, $currency, $description, $redirectUrl, $webhookUrl, $metadata)
    {
        $data = [
            'amount' => [
                'currency' => $currency,
                'value' => number_format($amount, 2, '.', ''),
            ],
            'description' => $description,
            'redirectUrl' => $redirectUrl,
            'webhookUrl' => $webhookUrl,
            'metadata' => $metadata
        ];

        return $this->request()->post('payments', $data)->json();
    }

    public function getPayment($id)
    {
        return $this->request()->get('payments/' . $id)->json();
    }

    private function request()
    {
        return Http::withToken($this->apiKey)->baseUrl('https://api.mollie.com/v2/')->asJson();
    }
}
