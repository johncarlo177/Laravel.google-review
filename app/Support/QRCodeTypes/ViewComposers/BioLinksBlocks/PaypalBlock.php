<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Models\QRCode;

class PaypalBlock extends LinkBlock
{
    public static function slug()
    {
        return 'paypal';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('paypal_qrcode_id');
    }

    public function getUrl()
    {
        $qrcodeId = $this->model->field('paypal_qrcode_id');

        $qrcode = QRCode::find($qrcodeId);

        if (empty($qrcode)) {
            return;
        }

        return url($qrcode->redirect->route);
    }
}
