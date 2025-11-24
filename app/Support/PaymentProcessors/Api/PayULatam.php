<?php

namespace App\Support\PaymentProcessors\Api;

use App\Support\System\Traits\WriteLogs;

class PayULatam
{
    use WriteLogs;

    private $mode, $accountId, $apiKey, $merchantId;

    public function __construct($mode, $accountId, $apiKey, $merchantId)
    {
        $this->mode = $mode;

        $this->accountId = $accountId;

        $this->apiKey = $apiKey;

        $this->merchantId = $merchantId;
    }

    public function verifyWebhook($data)
    {
        $value = $data['value'];

        if (preg_match('/\.00$/', $value)) {
            $value = intval($value) . '.0';
        }

        $signature = sprintf(
            "%s~%s~%s~%s~%s~%s",
            $this->apiKey,
            $this->merchantId,
            $data['reference_sale'],
            $value,
            $data['currency'],
            $data['state_pol']
        );

        $this->logDebugf('Raw signature = %s', $signature);

        $this->logDebugf('Expected sig = %s, response signature = %s', md5($signature), @$data['sign']);

        return md5($signature) === $data['sign'] && $data['response_message_pol'] === 'APPROVED';
    }

    public function getDataArray(
        $amount,
        $description,
        $referenceCode,
        $currency,
        $buyerEmail,
        $responseUrl,
        $confirmationUrl,
        $tax = null,
        $taxReturnBase = null,
    ) {

        $test = $this->isTest() ? 1 : 0;

        $data = [
            'accountId' => $this->accountId,
            'merchantId' => $this->merchantId,
            'description' => $description,
            'referenceCode' => $referenceCode,
            'amount' => $amount,
            'currency' => $currency,
            'buyerEmail' => $buyerEmail,
            'responseUrl' => $responseUrl,
            'confirmationUrl' => $confirmationUrl,
            'test' => $test,
            'signature' => $this->getCheckoutSignature($referenceCode, $amount, $currency)
        ];

        if ($tax !== null) {
            $data['tax'] = $tax;
        }

        if ($taxReturnBase !== null) {
            $data['taxReturnBase'] = $taxReturnBase;
        }

        return $data;
    }

    private function getCheckoutSignature($referenceCode, $amount, $currency)
    {
        return md5(sprintf('%s~%s~%s~%s~%s', $this->apiKey, $this->merchantId, $referenceCode, $amount, $currency));
    }

    public function getCheckoutUrl()
    {
        if ($this->mode != 'production') {
            return 'https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/';
        }

        return 'https://checkout.payulatam.com/ppp-web-gateway-payu/';
    }

    private function isTest()
    {
        return $this->mode !== 'production';
    }
}
