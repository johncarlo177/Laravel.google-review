<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Support\System\Traits\WriteLogs;

class iFrameBlock extends LinkBlock
{
    use WriteLogs;

    public static function slug()
    {
        return 'iframe';
    }
}
