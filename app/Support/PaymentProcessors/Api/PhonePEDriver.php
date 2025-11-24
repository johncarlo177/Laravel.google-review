<?php

namespace App\Support\PaymentProcessors\Api;

use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Http;

class PhonePEDriver
{
    use WriteLogs;

    public function __construct(
        protected $client_id,
        protected $client_version,
        protected $client_secret,
        protected $mode
    ) {}

    protected function isProduction()
    {
        return $this->mode === 'production';
    }

    protected function getAccessToken()
    {
        $sandboxUrl = 'https://api-preprod.phonepe.com/apis/pg-sandbox';
        $productionUrl = 'https://api.phonepe.com/apis/identity-manager';

        $endpoint = '/v1/oauth/token';

        $baseUrl = $this->isProduction() ? $productionUrl : $sandboxUrl;

        $version = $this->isProduction() ? $this->client_version : 1;

        $this->logDebug('Requesting token from %s', $baseUrl . $endpoint);

        $this->logDebug('mode = %s', $this->mode);

        $this->logDebug(
            'Credentials are client_id = %s, client_secret = %s, client_version = %s, mode = %s',
            $this->client_id,
            $this->client_secret,
            $version,
            $this->mode
        );

        $response = Http::baseUrl($baseUrl)
            ->asForm()
            ->post($endpoint, [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'client_version' => $version,
                'grant_type' => 'client_credentials',
            ]);

        $this->logDebug('Response %s', $response->body());

        return $response->json('access_token');
    }


    public function createPaymentLink($amount, $merchantId, $redirectUrl)
    {
        $sandboxUrl = 'https://api-preprod.phonepe.com/apis/pg-sandbox';

        $productionUrl = 'https://api.phonepe.com/apis/pg';

        $endpoint = '/checkout/v2/pay';

        $token = $this->getAccessToken();

        $this->logDebug('Access Token is %s', $token);

        $response = Http::asJson()
            ->baseUrl($this->isProduction() ? $productionUrl : $sandboxUrl)
            ->withHeader('Authorization', 'O-Bearer ' . $token)
            ->post($endpoint, [
                'merchantOrderId' => $merchantId,
                'amount' => $amount,
                'paymentFlow' => [
                    'type' => 'PG_CHECKOUT',
                    'merchantUrls' => [
                        'redirectUrl' => $redirectUrl
                    ]
                ]
            ]);

        $this->logWarning(
            'Could not get Phone PE payment link %s',
            $response->body()
        );

        if (!$response->json('redirectUrl')) {
            $this->logWarning(
                'Could not get Phone PE payment link %s',
                $response->body()
            );
        }

        return $response->json('redirectUrl');
    }
}
