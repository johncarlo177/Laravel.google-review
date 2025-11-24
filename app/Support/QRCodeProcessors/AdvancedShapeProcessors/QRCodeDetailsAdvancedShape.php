<?php

namespace App\Support\QRCodeProcessors\AdvancedShapeProcessors;

use App\Support\CompatibleSVG\SVGHelper;

class QRCodeDetailsAdvancedShape extends BaseAdvancedShapeProcessor
{
    protected $id = 'qrcode-details';

    private $s;

    protected function postProcess()
    {
        parent::postProcess();

        $this->s = new SVGHelper();

        $this->renderText(
            placeholderId: 'line_1',
            field: '',
            overrideText: @$this->qrcode->redirect->slug,
            overrideTextSize: static::TEXT_SIZE_MAX
        );

        $bg = $this->s->getById($this->svg, 'text_white_background');
        $l1 = $this->s->getById($this->svg, 'line_1');

        $frame = $this->s->getById($this->svg, 'frame');


        $backgroundColor = $this->isBackgroundEnabled()
            ? $this->qrcode->design->backgroundColor ?? '#ffffff' :
            'none';

        $frame->setStyle('fill', $backgroundColor);

        $bg->setStyle('fill', $backgroundColor);

        $l1->setStyle('fill', $backgroundColor);
        $l1->setStyle('stroke-width', '0');
    }
}
