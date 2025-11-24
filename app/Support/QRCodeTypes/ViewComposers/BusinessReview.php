<?php

namespace App\Support\QRCodeTypes\ViewComposers;

use App\Support\QRCodeTypes\BusinessReview\RedirectManager;

class BusinessReview extends Base
{
    public static function type()
    {
        return 'business-review';
    }

    public function finalReviewLink()
    {
        return RedirectManager::withQRCode(
            $this->getQRCode()
        )->getFinalReviewUrl();
    }

    public function getBannerBackgroundStyles()
    {
        if (empty($this->fileUrl('backgroundImage'))) {
            return;
        }

        return sprintf(
            '%s { background-image: url(%s); }',
            $this->typeSelector('.banner'),
            $this->fileUrl('backgroundImage')
        );
    }

    public function getLogoUrl()
    {
        if ($url = $this->fileUrl('logo')) {
            return $url;
        }

        return url('/assets/images/biolinks/default/logo.svg');
    }

    public function totalStars()
    {
        $value = $this->qrcodeData('totalNumberOfStars', 5);

        return range(1, $value);
    }

    public function starsBeforeRedirect()
    {
        $value = $this->qrcodeData('numberOfStarsToRedirect', 3);

        return $value;
    }
}
