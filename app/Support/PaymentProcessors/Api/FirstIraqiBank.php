<?php

namespace App\Support\PaymentProcessors\Api;

use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Http;

class FirstIraqiBank
{
    use WriteLogs;

    private $client_id, $client_secret, $isLive;

    public function __construct(
        string $client_id,
        string $client_secret,
        bool $isLive = false
    ) {
        $this->client_id = $client_id;

        $this->client_secret = $client_secret;

        $this->isLive = $isLive;
    }

    private function url($path)
    {
        if ($this->isLive) {
            // must return the live URL.
            return 'https://fib.prod.fib.iq/' . $path;
        }

        return 'https://fib.stage.fib.iq/' . $path;
    }

    public function generateAccessToken()
    {
        $url = 'auth/realms/fib-online-shop/protocol/openid-connect/token';

        $json = Http::asForm()
            ->post($this->url($url), [
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret
            ])->json();

        if (!@$json['access_token']) {
            $this->logWarning('Expected FIB response with access_token, but got %s', json_encode($json));
            return null;
        }

        $token = $json['access_token'];

        $this->logDebug('token = %s', $token);

        return $token;
    }

    public function createPayment($amount, $description, $callbackUrl)
    {
        $url = 'protected/v1/payments';

        return Http::withToken($this->generateAccessToken())
            ->asJson()
            ->acceptJson()
            ->post($this->url($url), [
                'monetaryValue' => [
                    'amount' => $amount,
                    'currency' => 'IQD'
                ],
                'statusCallbackUrl' => $callbackUrl,
                'description' => $description
            ])->json();
    }
}
