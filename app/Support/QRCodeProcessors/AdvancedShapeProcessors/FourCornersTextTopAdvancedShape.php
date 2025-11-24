<?php

namespace App\Support\QRCodeProcessors\AdvancedShapeProcessors;

use SVG\Nodes\Structures\SVGStyle;

class FourCornersTextTopAdvancedShape extends BaseAdvancedShapeProcessor
{
    protected $id = 'four-corners-text-top';

    protected function postProcess()
    {
        parent::postProcess();

        $this->renderFrameColor();
    }


    protected function renderFrameColor()
    {
        $defs = $this->doc->getElementsByTagName('defs')[0];

        $style = new SVGStyle(
            sprintf('
            .corner {
                stroke: %s!important;
            }
        ', $this->qrcode->design->advancedShapeFrameColor)
        );

        $this->addChild($defs, $style);
    }
}
