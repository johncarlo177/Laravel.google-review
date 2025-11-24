<?php namespace App\Interfaces;

use App\Models\QRCode;

interface QRCodeStats {
    public function getStats(QRCode $qrcode);
}