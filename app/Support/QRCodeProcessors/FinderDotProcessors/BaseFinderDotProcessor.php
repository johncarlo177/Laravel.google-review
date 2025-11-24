<?php

namespace App\Support\QRCodeProcessors\FinderDotProcessors;

use App\Support\QRCodeProcessors\FinderProcessors\BaseFinderProcessor;

abstract class BaseFinderDotProcessor extends BaseFinderProcessor
{
    protected function shouldProcess(): bool
    {
        return $this->qrcode->design->finderDot == $this->id;
    }

    protected function symbolSvgId()
    {
        return 'symbol-finder-dot';
    }

    protected function maskSvgId()
    {
        return 'mask-finder-dot';
    }
}
