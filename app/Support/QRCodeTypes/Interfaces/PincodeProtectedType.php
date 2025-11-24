<?php

namespace App\Support\QRCodeTypes\Interfaces;

use App\Models\QRCode;

interface PincodeProtectedType
{
    public function shouldProtectByPincode(QRCode $qrcode): bool;

    public function renderPincodeProtectionPage(QRCode $qrcode);
}
