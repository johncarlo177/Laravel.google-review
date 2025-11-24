<?php

namespace App\Interfaces;

use App\Models\QRCode;

interface QRCodeDataMaker
{
    public function init(QRCode $qrcode): self;

    public function make(): string;
}
