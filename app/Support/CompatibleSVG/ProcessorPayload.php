<?php

namespace App\Support\CompatibleSVG;

use App\Models\QRCode;
use SVG\SVG;

class ProcessorPayload
{
    public QRCode $qrcode;

    public SVG $svg;
}
