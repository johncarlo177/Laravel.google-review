<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;
use App\Models\QRCodeRedirect;
use App\Support\QRCodeTypes\Ads\AdsManager;
use App\Support\QRCodeTypes\Interfaces\PincodeProtectedType;
use App\Support\QRCodeTypes\Interfaces\ShouldImmediatlyRedirectToDestination;
use App\Support\QRCodeTypes\Traits\HasImmediateRedirect;
use App\Support\QRCodeTypes\Traits\ProtectByPincode;
use App\Support\QRCodeTypes\ViewComposers\Cache\QRCodeCacheManager;

abstract class BaseDynamicType extends BaseType  implements PincodeProtectedType
{
    protected AdsManager $ads;

    use ProtectByPincode, HasImmediateRedirect;

    public function __construct()
    {
        $this->ads = new AdsManager;
    }

    public function isDynamic(): bool
    {
        return true;
    }

    public function makeData(QRCode $qrcode): string
    {
        $redirect = QRCodeRedirect::where('qrcode_id', $qrcode->id)->first();

        return $redirect->route;
    }


    public function renderView(QRCode $qrcode)
    {
        if ($this->ads->withQRCode($qrcode)->shouldShowAds()) {
            return $this->ads->render();
        }

        if ($this instanceof PincodeProtectedType) {
            if ($this->shouldProtectByPincode($qrcode)) {
                return $this->renderPincodeProtectionPage($qrcode);
            }
        }

        if ($this instanceof ShouldImmediatlyRedirectToDestination) {
            return $this->renderImmediateRedirect($qrcode);
        }

        return QRCodeCacheManager::withQRCode($qrcode)->render();
    }
}
