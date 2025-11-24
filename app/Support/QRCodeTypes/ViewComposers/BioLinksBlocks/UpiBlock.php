<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Models\QRCode;
use App\Support\System\Traits\WriteLogs;
use App\Support\UPIPaymentUrlGenerator;

class UpiBlock extends LinkBlock
{
    use WriteLogs;

    public static function slug()
    {
        return 'upi';
    }

    public function url()
    {
        /**
         * @var QRCode
         */
        $qrcode = QRCode::find($this->model->field('upi_qrcode_id'));

        if (!$qrcode?->redirect?->route) {
            return '';
        }

        return url($qrcode->redirect->route);
    }

    public function linkTarget()
    {
        return '_blank';
    }
}
