<?php

namespace App\Support\QRCodeProcessors;

use App\Interfaces\QRCodeProcessor;
use App\Support\AI\AIQRCodeGenerator;
use SVG\SVG;

class AiSvgBuilder extends BaseProcessor implements QRCodeProcessor
{
    protected AIQRCodeGenerator $aiGenerator;

    public function __construct()
    {
        $this->aiGenerator = new AIQRCodeGenerator();
    }

    protected function shouldPostProcess()
    {
        return false;
    }

    protected function shouldProcess(): bool
    {
        return $this->aiGenerator->hasAiDesign($this->qrcode);
    }

    protected function process()
    {
        $this->output->svgString = $this->aiGenerator->buildSvgString($this->qrcode);

        $this->output->svg = SVG::fromString($this->output->svgString);
    }
}
