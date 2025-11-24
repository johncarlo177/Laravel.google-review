<?php

namespace App\Support\QRCodeTypes\Traits;

use App\Models\QRCode;
use App\Plugins\PluginManager;

trait ProtectByPincode
{
    public function shouldProtectByPincode(QRCode $qrcode): bool
    {
        $shouldProtect = $this->qrcodeHasPinCode($qrcode) && !$this->pincodeIsValid($qrcode);

        $shouldProtect = PluginManager::doFilter(
            PluginManager::FILTER_SHOULD_PROTECT_QRCODE_BY_PINCODE,
            $shouldProtect,
            $qrcode
        );

        return $shouldProtect;
    }

    private function pincodeIsValid(QRCode $qrcode)
    {
        $pincode = request()->post('pincode');

        return $pincode === $qrcode->pincode;
    }

    private function qrcodeHasPinCode(QRCode $qrcode)
    {
        return !empty($qrcode->pincode);
    }

    public function renderPincodeProtectionPage(QRCode $qrcode)
    {
        if ($this->qrcodeHasPinCode($qrcode) && !$this->pincodeIsValid($qrcode) && !empty(request()->post('pincode'))) {
            return redirect()->back()->with('error', t('Invalid PIN Code'));
        }

        return view('qrcode.pages.pincode');
    }
}
