<?php

namespace App\Support\PaymentProcessors\Api;

use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Http;


class OrangeBF
{
    use WriteLogs;

    private $mode, $merchant, $username, $password;

    public function __construct($mode, $merchant, $username, $password)
    {
        $this->mode = $mode;
        $this->merchant = $merchant;
        $this->username = $username;
        $this->password = $password;
    }

    private function mockResponse()
    {
        sleep(2);

        return json_decode('{
            "status": "200",
            "message": "[9014_MERCODE]Cher client, votre paiement sur subscription-39 de  1000 FCFA a ELITES MULTI SERVICES a ete effectue avec succes. Votre solde:  491000 FCFA. ID Trans: OM230405.1954.C00022",
            "transID": "OM230405.1954.C00022"
        }  ', true);
    }

    public function verifyPayment($mobileNumber, $otp, $amount, $referenceNumber, $extTransactionId)
    {
        if (app()->environment('local')) {
            return $this->mockResponse();
        }

        $template = '
<COMMAND>
    <TYPE>OMPREQ</TYPE> 
    <customer_msisdn>%s</customer_msisdn> 
    <merchant_msisdn>%s</merchant_msisdn> 
    <api_username>%s</api_username> 
    <api_password>%s</api_password> 
    <amount>%s</amount> 
    <PROVIDER>101</PROVIDER> 
    <PROVIDER2>101</PROVIDER2> 
    <PAYID>12</PAYID>
    <PAYID2>12</PAYID2>
    <otp>%s</otp> 
    <reference_number>%s</reference_number> 
    <ext_txn_id>%s</ext_txn_id>
</COMMAND>';

        $xml = sprintf(
            $template,
            $mobileNumber,
            $this->merchant,
            $this->username,
            $this->password,
            intval($amount),
            $otp,
            $referenceNumber,
            $extTransactionId
        );

        $response = $this->send($xml);

        $response = "<response>$response</response>";

        $result = simplexml_load_string(
            $response
        );

        return (array) $result;
    }

    private function send($xml)
    {
        $url = $this->base(); // URL to make some test
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    private function base()
    {
        return $this->mode === 'test' ? 'https://testom.orange.bf:9008/payment' : 'https://apiom.orange.bf:9007/payment';
    }

    private function request($xml)
    {
        $base = $this->mode === 'test' ? 'https://testom.orange.bf:9008/payment' : 'https://apiom.orange.bf:9007/payment';

        $this->logDebug(
            sprintf("Sending request to %s\n%s", $base, $xml)
        );

        return Http::contentType("text/xml; charset=UTF8")
            ->bodyFormat('none')->post($base, [
                'body' => $xml
            ]);
    }
}
