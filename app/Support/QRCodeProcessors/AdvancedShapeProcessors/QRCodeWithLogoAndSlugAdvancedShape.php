<?php

namespace App\Support\QRCodeProcessors\AdvancedShapeProcessors;

use App\Support\CompatibleSVG\SVGHelper;
use App\Support\System\Traits\WriteLogs;
use SVG\Nodes\Embedded\SVGImage;

class QRCodeWithLogoAndSlugAdvancedShape extends BaseAdvancedShapeProcessor
{
    use WriteLogs;

    protected $id = 'qrcode-with-logo-and-slug';

    private SVGHelper $s;

    protected function postProcess()
    {
        $this->logDebug('Post processing ...');

        parent::postProcess();

        $this->logDebug('After parent post processing ...');

        $this->s = new SVGHelper();

        $this->renderText(
            placeholderId: 'line_1',
            field: '',
            overrideText: @$this->qrcode->redirect->slug,
            overrideTextSize: static::TEXT_SIZE_MAX
        );

        $background = $this->s->getById($this->svg, 'background');

        $l1 = $this->s->getById($this->svg, 'line_1');

        $backgroundColor = $this->isBackgroundEnabled()
            ? $this->qrcode->design->backgroundColor ?? '#ffffff' :
            'none';

        $background->setStyle('fill', $backgroundColor);

        $l1->setStyle('fill', $backgroundColor);
        $l1->setStyle('stroke-width', '0');

        $this->logDebug('Before rendering logo');

        $this->renderLogo();
    }

    protected function logoPlaceholder()
    {
        return $this->s->getById($this->svg, 'logo_placeholder');
    }

    protected function removeLogoPlaceholder()
    {
        $box = $this->logoPlaceholder();

        $box->getParent()->removeChild($box);
    }

    protected function renderLogo()
    {
        if (!$this->getLogoSourceURL()) {


            return $this->removeLogoPlaceholder();
        }

        $box = $this->logoPlaceholder();

        $logo = new SVGImage();

        $logo->setAttribute('x', $box->getAttribute('x'));

        $logo->setAttribute('y', $box->getAttribute('y'));

        $logo->setAttribute('width', $box->getAttribute('width'));

        $logo->setAttribute('height', $box->getAttribute('height'));

        $logo->setAttribute('xlink:href', $this->getLogoSourceURL());

        $box->getParent()->addChild($logo);

        $this->removeLogoPlaceholder();
    }

    protected function getLogoSourceURL()
    {
        $id = $this->designValue('sticker_logo');

        return file_url($id);
    }
}
