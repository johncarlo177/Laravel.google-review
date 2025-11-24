<?php

namespace App\Support\PaymentProcessors\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayUInternational
{
    private $mode, $pos_id, $second_key, $client_id, $client_secret;

    public function __construct($mode, $pos_id, $second_key, $client_id, $client_secret)
    {
        $this->mode = $mode;

        $this->pos_id = $pos_id;

        $this->second_key = $second_key;

        $this->client_id = $client_id;

        $this->client_secret = $client_secret;
    }

    public function getOrder($orderId)
    {
        $response = $this->authorizedRequest()
            ->get('api/v2_1/orders/' . $orderId);

        return @$response['orders'][0];
    }

    public function createOrder(
        $notifyUrl,
        $continueUrl,
        $customerIp,
        $description,
        $currencyCode,
        $totalAmount,
        $extOrderId,
        $buyer_email,
        $product_name,
        $product_unitPrice,
        $product_quantity
    ) {

        $data = array(
            'notifyUrl' => $notifyUrl,
            'continueUrl' => $continueUrl,
            'customerIp' => $customerIp,
            'merchantPosId' => $this->pos_id,
            'description' => $description,
            'currencyCode' => $currencyCode,
            'totalAmount' => intval($totalAmount * 100),
            'extOrderId' => $extOrderId,
            'buyer' =>
            array(
                'email' => $buyer_email,
            ),
            'products' =>
            array(
                array(
                    'name' => $product_name,
                    'unitPrice' => intval($product_unitPrice * 100),
                    'quantity' => $product_quantity,
                ),
            ),
        );

        $response = $this->authorizedRequest()
            ->post('api/v2_1/orders', $data);

        return $response->json();
    }

    private function authorizedRequest()
    {
        $token = $this->getToken();

        return Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->baseUrl($this->url(''))
            ->withOptions([
                'allow_redirects' => false
            ]);
    }

    public function getToken()
    {
        $response = Http::asForm()->post($this->url('pl/standard/user/oauth/authorize'), [
            'grant_type' => 'client_credentials',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        ])->json();


        return @$response['access_token'];
    }

    private function url($path)
    {
        if ($this->mode === 'sandbox') {
            return 'https://secure.snd.payu.com/' . $path;
        }

        return 'https://secure.payu.com/' . $path;
    }
}
