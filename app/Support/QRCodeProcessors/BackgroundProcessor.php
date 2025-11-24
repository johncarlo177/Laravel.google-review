<?php

namespace App\Support\QRCodeProcessors;

use Illuminate\Support\Facades\Log;
use SVG\Nodes\Shapes\SVGRect;

class BackgroundProcessor extends BaseProcessor
{
    protected function shouldProcess(): bool
    {
        return false;
    }

    protected function shouldPostProcess()
    {
        return $this->qrcode->design->backgroundEnabled;
    }

    protected function process()
    {
    }

    protected function postProcess()
    {

        $origin = $this->getViewBoxStart();

        $length = $this->getSvgViewBoxSize();

        $background = new SVGRect($origin, $origin, $length, $length);

        $background->setAttribute('fill', $this->qrcode->design->backgroundColor);

        $this->doc->addChild($background, 0);
    }

    protected function _postProcess()
    {
        $foregroundGroup = $this->doc->getElementsByClassName('foreground-0')[0];

        $origin = $this->getViewBoxStart();

        $length = $this->getSvgViewBoxSize();

        $background = new SVGRect($origin, $origin, $length, $length);

        $background->setAttribute('fill', $this->qrcode->design->backgroundColor);

        $this->addChild($foregroundGroup, $background, 0);
    }
}
