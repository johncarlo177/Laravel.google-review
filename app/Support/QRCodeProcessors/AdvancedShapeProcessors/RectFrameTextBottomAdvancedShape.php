<?php

namespace App\Support\QRCodeProcessors\AdvancedShapeProcessors;

class RectFrameTextBottomAdvancedShape extends BaseAdvancedShapeProcessor
{
    protected $id = 'rect-frame-text-bottom';

    protected function postProcess()
    {
        parent::postProcess();

        $this->renderDropShadow();
    }

    protected function renderDropShadow()
    {
        if ($this->qrcode->design->advancedShapeDropShadow) {
            // Drop shadow rendered by default.
            return;
        }

        $frame = $this->doc->getElementsByClassName('frame')[0];

        $frame->getParent()->setStyle('filter', 'none');
    }
}
