<?php

namespace App\Support\PaymentProcessors\Api;

use Illuminate\Support\Facades\Http;

class PayStack
{
    private $publicKey, $secretKey;

    public function __construct($publicKey, $secretKey)
    {
        $this->publicKey = $publicKey;

        $this->secretKey = $secretKey;
    }

    public function createTransaction($amount, $email, $metadata, $callback_url)
    {
        $response = $this->request()->post('transaction/initialize', [
            'amount' => $amount * 100,
            'email' => $email,
            'callback_url' => $callback_url,
            'metadata' => json_encode($metadata)
        ]);

        return $response->json();
    }

    private function request()
    {
        return Http::asJson()->acceptJson()->baseUrl('https://api.paystack.co/')->withToken($this->secretKey);
    }
}
