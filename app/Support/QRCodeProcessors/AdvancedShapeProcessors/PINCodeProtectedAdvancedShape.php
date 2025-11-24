<?php

namespace App\Support\QRCodeProcessors\AdvancedShapeProcessors;

use App\Support\CompatibleSVG\SVGHelper;

class PINCodeProtectedAdvancedShape extends BaseAdvancedShapeProcessor
{
    protected $id = 'pincode-protected';

    private $s;

    protected function shouldPostProcess()
    {
        return parent::shouldPostProcess() && !empty($this->qrcode->pincode);
    }

    protected function postProcess()
    {
        parent::postProcess();

        $this->s = new SVGHelper();

        $this->renderText(
            placeholderId: 'line_1',
            field: '',
            overrideText: t('PIN CODE'),
            overrideTextSize: static::TEXT_SIZE_MAX,
            overrideTextColor: '#000000'
        );

        $this->renderText(
            placeholderId: 'line_2',
            field: '',
            overrideText: $this->qrcode->pincode,
            overrideTextSize: static::TEXT_SIZE_MAX,
            overrideTextColor: '#000000'
        );

        $bg = $this->s->getById($this->svg, 'text_white_background');
        $l1 = $this->s->getById($this->svg, 'line_1');
        $l2 = $this->s->getById($this->svg, 'line_2');

        $bg->setStyle('fill', '#ffffff');

        $l1->setStyle('fill', '#ffffff');
        $l1->setStyle('stroke-width', '0');

        $l2->setStyle('fill', '#ffffff');
        $l2->setStyle('stroke-width', '0');
    }
}
