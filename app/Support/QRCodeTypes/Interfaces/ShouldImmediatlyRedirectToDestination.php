<?php

namespace App\Support\QRCodeTypes\Interfaces;

use App\Models\QRCode;

interface ShouldImmediatlyRedirectToDestination
{
    public function makeDestination(QRCode $qrcode): string;

    public function renderImmediateRedirect(QRCode $qrcode);
}
