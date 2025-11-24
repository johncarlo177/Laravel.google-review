<?php

namespace App\Support\PaymentProcessors\Api;

use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Http;

class Dintero
{
    use WriteLogs;

    private $accountId, $clientId, $clientSecret, $vat, $profileId;

    private bool $testing = true;

    public function __construct(
        $accountId,
        $clientId,
        $clientSecret,
        $profileId,
        $vat,
        bool $testing
    ) {
        $this->accountId = $accountId;

        $this->clientId = $clientId;

        $this->clientSecret = $clientSecret;

        $this->profileId = $profileId;

        $this->vat = $vat;

        $this->testing = $testing;
    }

    private function getAccessToken()
    {
        $response = Http::asJson()
            ->acceptJson()
            ->withBasicAuth(
                $this->clientId,
                $this->clientSecret
            )
            ->post(
                $this->url(
                    sprintf(
                        '/accounts/%s%s/auth/token',
                        $this->testing ? 'T' : 'P',
                        $this->accountId
                    )
                ),
                [
                    'grant_type' => 'client_credentials',
                    'audience' => sprintf(
                        'https://api.dintero.com/v1/accounts/%s%s',
                        $this->testing ? 'T' : 'P',
                        $this->accountId
                    )
                ]
            );

        return $response->json();
    }

    public function createSession(
        $successUrl,
        $callbackUrl,
        $email,
        $amount,
        $currency,
        $itemId,
        $itemDescription,
    ) {
        $vatAmount = $amount * $this->vat;

        $amount = $amount * 100 + $vatAmount;

        $amount = doubleval($amount);

        $vat = doubleval($this->vat);

        $data = [
            'url' => [
                'return_url' => $successUrl,
                'callback_url' => "$callbackUrl?sid_parameter_name=sid",

            ],
            'customer' => [
                'email' => $email,
            ],
            'order' => [
                'amount' => $amount,
                'currency' => $currency,
                'merchant_reference' => $itemId,
                'vat_amount' => $vatAmount,
                'items' => [
                    [
                        'id' => $itemId,
                        'line_id' => $itemId,
                        'description' => $itemDescription,
                        'vat_amount' => $vatAmount,
                        'vat' => $vat,
                        'type' => 'digital',
                        'amount' => $amount,
                        'quantity' => 1
                    ]
                ]

            ],
            'profile_id' => $this->profileId
        ];

        $response = $this->request()->post(
            $this->url('sessions-profile'),
            $data
        );

        return $response->json();
    }

    private function request()
    {
        $token = $this->getAccessToken();

        $t = @$token['access_token'];

        return Http::acceptJson()
            ->withToken($t)
            ->asJson();
    }

    private function url($url)
    {
        if (@$url[0] === '/') {
            $url = substr($url, 1);
        }

        return 'https://checkout.dintero.com/v1/' . $url;
    }
}
