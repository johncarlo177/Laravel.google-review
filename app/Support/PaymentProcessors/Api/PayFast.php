<?php

namespace App\Support\PaymentProcessors\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayFast
{
    private $mode, $merchantId, $merchantKey, $passphrase;

    public function __construct($mode, $merchantId, $merchantKey, $passphrase)
    {
        $this->mode = $mode;
        $this->merchantId = $merchantId;
        $this->merchantKey = $merchantKey;
        $this->passphrase = $passphrase;
    }

    public function createPaymentLink(
        $item_name,
        $amount,
        $email,
        $return_url,
        $cancel_url,
        $notify_url,
        $custom_str1
    ) {
        $data = [
            'merchant_id' => $this->merchantId,
            'merchant_key' => $this->merchantKey,
            'amount' => $amount,
            'email_address' => $email,
            'item_name' => $item_name,
            'return_url' => $return_url,
            'cancel_url' => $cancel_url,
            'notify_url' => $notify_url,
            'custom_str1' => $custom_str1
        ];

        $response = $this->request()
            ->post(
                'eng/process',
                $data
            );

        $link = $response->header('Location');

        if (empty($link)) {
            Log::error('Expected PayFast location link but received none.');
            Log::error('PayFast response is: ' . $response->body());
            return null;
        }

        return $link;
    }

    public function verifyWebhookData($data)
    {
        $pfParamString = '';

        $pfData = $data;

        // Strip any slashes in data
        foreach ($pfData as $key => $val) {
            $pfData[$key] = stripslashes($val);
        }

        // Convert posted variables to a string
        foreach ($pfData as $key => $val) {
            if ($key !== 'signature') {
                $pfParamString .= $key . '=' . urlencode($val) . '&';
            } else {
                break;
            }
        }

        $pfParamString = substr($pfParamString, 0, -1);

        // Variable initialization
        $url = $this->url('eng/query/validate');

        // Create default cURL object
        $ch = curl_init();

        // Set cURL options - Use curl_setopt for greater PHP compatibility
        // Base settings
        curl_setopt($ch, CURLOPT_USERAGENT, NULL);  // Set user agent
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);      // Return output as string rather than outputting it
        curl_setopt($ch, CURLOPT_HEADER, false);             // Don't include header in output
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        // Standard settings
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $pfParamString);

        if (!empty($pfProxy))
            curl_setopt($ch, CURLOPT_PROXY, $pfProxy);

        // Execute cURL
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === 'VALID') {
            return true;
        }

        Log::error('Webhook validation response = ' . $response);

        return false;
    }

    private function request()
    {
        return Http::asForm()
            ->baseUrl($this->url())
            ->withOptions([
                'allow_redirects' => false
            ]);
    }

    private function url($path = '')
    {
        if ($this->mode == 'sandbox') {
            return 'https://sandbox.payfast.co.za/' . $path;
        }

        return 'https://www.payfast.co.za/' . $path;
    }
}
