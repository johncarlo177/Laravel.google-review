<?php

namespace App\Support\QRCodeTypes\ViewComposers;

use App\Support\Color;
use App\Support\QRCodeTypes\ViewComposers\Traits\GeneratesGradientBackground;
use App\Support\QRCodeTypes\ViewComposers\Traits\HasSocialIcons;
use App\Support\QRCodeTypes\ViewComposers\Traits\HasWhiteCards;

class AppDownload extends Base
{
    use HasSocialIcons;
    use HasWhiteCards;
    use GeneratesGradientBackground;

    public static function type()
    {
        return 'app-download';
    }

    public function favicon()
    {
        return $this->fileUrl('favicon');
    }

    public function detailsContainerStyles()
    {
        return $this->generateGradientBackground(
            '.qrcode-type-app-download .details-container',
            'backgroundColor'
        );
    }

    protected function getTemplate()
    {
        return 'template';
    }

    public function mainDetailsStyles()
    {
        $color = $this->designValue('textColor');

        if (empty($color)) {
            return null;
        }

        $selector = 'html .qrcode-type-app-download .main-details';

        $shortSelector = 'html .qrcode-type-app-download';

        $pattern = "$selector p, $selector h1, $shortSelector .portfolio-title { color: %s; }";

        return sprintf($pattern, $color);
    }
}
