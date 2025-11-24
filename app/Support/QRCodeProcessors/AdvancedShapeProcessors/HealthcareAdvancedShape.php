<?php

namespace App\Support\QRCodeProcessors\AdvancedShapeProcessors;

class HealthcareAdvancedShape extends BaseAdvancedShapeProcessor
{
    protected $id = 'healthcare';

    protected function postProcess()
    {
        parent::postProcess();

        $this->bindElementFill('main-frame', 'healthcareFrameColor');

        $this->bindElementFill('text_background', 'textBackgroundColor');

        $this->bindElementFill('heart-path', 'healthcareHeartColor');

        $this->hideTextPlaceholder();
    }

    private function hideTextPlaceholder()
    {
        $this->doc->getElementById('text_placeholder')->setStyle('opacity', '0');
    }
}
