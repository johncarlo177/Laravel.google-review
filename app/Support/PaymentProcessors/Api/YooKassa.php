<?php

namespace App\Support\PaymentProcessors\Api;

use YooKassa\Client;

class YooKassa
{
    private $clientId, $clientSecret;

    public function __construct($clientId, $clientSecret)
    {
        $this->clientId = $clientId;

        $this->clientSecret = $clientSecret;
    }

    public function createPayLink(
        $amount,
        $description,
        $return_url,
        $payer_email,
        $metadata
    ) {

        $client = new Client();

        $client->setAuth($this->clientId, $this->clientSecret);


        $payment = $client->createPayment([
            'amount' => [
                'value' => $amount,
                'currency' => 'RUB',
            ],
            'description' => $description, // Прописываем нужное описание
            'capture' => true,
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => $return_url, // Задаём страницу на которую пользователь вернётся если нажмёт книпку вернутся в магазин на сайте yooMoney
            ],
            'receipt' => array(
                'customer' => array(
                    'email' => $payer_email,
                ),
                'items' => array(
                    array(
                        'description' => $description,
                        'quantity' => '1.00',
                        'amount' => array(
                            'value' => $amount,
                            'currency' => 'RUB'
                        ),
                        'vat_code' => '1',
                        'payment_mode' => 'full_payment',
                        'payment_subject' => 'service',
                        'measure' => 'piece'
                    )
                )
            ),
            'metadata' => $metadata,
        ], uniqid('', true));

        return $payment->getConfirmation()->getConfirmationUrl();
    }
}
