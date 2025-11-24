<?php

namespace App\Support\QRCodeTypes\ViewComposers;

use App\Support\UPIPaymentUrlGenerator;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class UpiDynamic extends Base
{
    public static function type()
    {
        return 'upi-dynamic';
    }

    public function text()
    {
        return $this->designField('text');
    }

    public function getBannerBackgroundStyles()
    {
        if (empty($this->fileUrl('backgroundImage'))) {
            return;
        }

        return sprintf(
            '%s { background-image: url(%s); }',
            $this->typeSelector('.banner'),
            $this->fileUrl('backgroundImage')
        );
    }

    public function getLogoUrl()
    {
        if ($url = $this->fileUrl('logo')) {
            return $url;
        }

        return url('/assets/images/biolinks/default/logo.svg');
    }

    public function getAmount()
    {
        return request()->input('amount') ?? $this->qrcodeData('amount');
    }

    public function providers()
    {
        return UPIPaymentUrlGenerator::getUiProviders();
    }

    public function paymentUrl($scheme = null)
    {
        return UPIPaymentUrlGenerator::withUpiId(
            $this->qrcodeData('upi_id')
        )
            ->withAmount($this->getAmount())
            ->withScheme($scheme)
            ->withMerchantName($this->qrcodeData('payee_name'))
            ->generateUrl();
    }

    public function renderUpiQRCode()
    {
        $options = new QROptions([
            'version'             => QRCode::VERSION_AUTO,
            'outputType'          => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel'            => EccLevel::H,
            'addQuietzone'        => false,
            'imageBase64'         => false,
            // if set to true, the light modules won't be rendered
        ]);

        $qrcode = new QRCode($options);

        return $qrcode->render($this->paymentUrl());
    }
}
