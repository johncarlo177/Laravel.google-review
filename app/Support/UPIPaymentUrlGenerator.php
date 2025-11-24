<?php

namespace App\Support;

class UPIPaymentUrlGenerator
{

    protected $payee_name,
        $upi_id,
        $amount;

    protected $scheme = null;

    public static function withUpiId($id)
    {
        $instance = new static;

        $instance->upi_id = $id;

        return $instance;
    }

    public function withMerchantName($name)
    {
        $this->payee_name = $name;
        return $this;
    }

    public function withAmount($amount)
    {
        $this->amount =  $amount;

        return $this;
    }

    public function withScheme($scheme)
    {
        $this->scheme = $scheme;

        return $this;
    }

    public function generateUrl()
    {
        $params = [
            'pa' => $this->upi_id,
            'pn' => $this->payee_name,
            'tr' => ' ', // Transaction id, required for google pay links.
        ];

        if ($this->amount) {
            $params['am'] = $this->amount;
            $params['cu'] = 'INR';
        }

        $string = http_build_query($params);

        $result = sprintf(
            '%spay?%s',
            $this->scheme,
            $string
        );

        return $result;
    }

    protected function getScheme()
    {
        if (!$this->scheme) {
            return 'upi://';
        }

        return $this->scheme;
    }

    public static function getUiProviders()
    {
        return collect(
            static::getProviders()
        )
            ->filter(
                function ($provider) {
                    return !preg_match('/general/i', $provider['name']);
                }
            )
            ->values()
            ->all();
    }

    public static function getProviders()
    {
        return [
            [
                'name' => 'General UPI',
                'scheme' => 'upi://',
            ],
            [
                'name' => 'Google Pay',
                'scheme' => 'gpay://upi/',
                'image' => url('assets/images/upi-icons/google-pay.png')
            ],
            [
                'name' => 'PhonePe',
                'scheme' => 'phonepe://',
                'image' => url('assets/images/upi-icons/phonepe.png')
            ],
            [
                'name' => 'Paytm',
                'scheme' => 'paytmmp://',
                'image' => url('assets/images/upi-icons/paytm.png')
            ],
            [
                'name' => 'Amazon Pay',
                'scheme' => 'amazonpay://',
                'image' => url('assets/images/upi-icons/amazonpay.png')
            ],
            [
                'name' => 'BHIM',
                'scheme' => 'bhim://upi/',
                'image' => url('assets/images/upi-icons/bhim.png')
            ],
        ];
    }
}
