<?php

namespace App\Policies;

use App\Http\Middleware\ErrorMessageMiddleware;
use App\Models\QRCode;

class QRCodeModificationRestriction
{
    private $resitrectedIdsInDemo = [
        130001
    ];

    private QRCode $qrcode;

    public static function make(QRCode $qrcode)
    {

        $instance = new static;

        $instance->qrcode = $qrcode;

        return $instance;
    }

    public function applyRestrictions()
    {
        if ($this->isRestricted()) {
            ErrorMessageMiddleware::abortWithMessage('Modification of this QR code is restricted.');
        }
    }

    public function isRestricted()
    {
        return app()->environment('demo') &&
            collect(
                $this->resitrectedIdsInDemo
            )->filter(function ($id) {
                return $id == $this->qrcode->id;
            })->isNotEmpty();
    }
}
