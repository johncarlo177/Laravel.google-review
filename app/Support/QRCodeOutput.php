<?php

namespace App\Support;

use App\Models\QRCode;
use SVG\SVG;

class QRCodeOutput
{
    public QRCode $qrcode;
    public string $svgString;
    public SVG $svg;
    public int $size;
    public string $data;
    public int $round;
    public bool $renderText;

    public function __construct(
        QRCode $qrcode,
        string $svgString,
        int $size,
        string $data,
        int $round = 0,
        bool $renderText = true,
    ) {
        $this->qrcode = $qrcode;
        $this->svgString = $svgString;
        $this->size = $size;
        $this->data = $data;
        $this->round = $round;
        $this->renderText = $renderText;
    }

    public function __toString()
    {
        return $this->svg;
    }
}
