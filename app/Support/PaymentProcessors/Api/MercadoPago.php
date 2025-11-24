<?php

namespace App\Support\PaymentProcessors\Api;

use Illuminate\Support\Facades\Http;

class MercadoPago
{
    private $accessToken;

    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;
    }


    public function getPayments()
    {
        $response = $this->request()->get(
            'v1/payments/search?sort=date_created&criteria=desc&external_reference=ID_REF'
        )->json();

        return $response;
    }

    public function getPayment($id)
    {
        $response = $this->request()->get(
            'v1/payments/' . $id
        )->json();

        return $response;
    }

    private function request()
    {
        return Http::withToken($this->accessToken)
            ->acceptJson()
            ->asJson()
            ->baseUrl('https://api.mercadopago.com/');
    }

    public function createPreference(
        $title,
        $quantity,
        $unitPrice,
        $metadata,
        $notificationUrl,
        $successUrl,
        $failureUrl
    ) {

        return $this->request()->post('checkout/preferences', [
            'items' => [
                [
                    'title' => $title,
                    'quantity' => $quantity,
                    'unit_price' => (float)$unitPrice,
                ]
            ],
            'payment_methods' => [
                'installments' => 1,
            ],
            'metadata' => $metadata,
            'back_urls' => [
                'success' => $successUrl,
                'failure' => $failureUrl
            ],
            'notification_url' => $notificationUrl
        ])->json();
    }
}
