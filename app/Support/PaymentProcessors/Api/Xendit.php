<?php

namespace App\Support\PaymentProcessors\Api;

use Illuminate\Support\Facades\Http;

class Xendit
{
    private $publicKey, $secretKey;

    public function __construct($publicKey, $secretKey)
    {
        $this->publicKey = $publicKey;

        $this->secretKey = $secretKey;
    }

    public function getInvoice($id)
    {
        return $this->request()->get('invoices/' . $id)->json();
    }

    public function createInvoice(
        $external_id,
        $amount,
        $description,
        $payer_email,
        $success_redirect_url,
        $failure_redirect_url
    ) {
        return $this->request()->post(
            'invoices',
            compact(
                'external_id',
                'amount',
                'description',
                'payer_email',
                'success_redirect_url',
                'failure_redirect_url'
            )
        )->json();
    }


    private function request()
    {
        return Http::withBasicAuth($this->secretKey, '')->baseUrl('https://api.xendit.co/v2/');
    }
}
