<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Rules\UrlRule;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssGradientGenerator;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssRuleGenerator;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\TextFontStyle;

class ShareBlock extends LinkBlock
{
    public static function slug()
    {
        return 'share';
    }
}
