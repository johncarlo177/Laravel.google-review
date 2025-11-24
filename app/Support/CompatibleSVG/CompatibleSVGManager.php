<?php

namespace App\Support\CompatibleSVG;

use App\Models\QRCode;
use App\Support\CompatibleSVG\Processors\BaseProcessor;
use App\Support\System\Traits\ClassListLoader;
use Illuminate\Pipeline\Pipeline;

class CompatibleSVGManager
{
    use ClassListLoader;

    private QRCode $qrcode;

    public function __construct(QRCode $qrcode)
    {
        $this->qrcode = $qrcode;
    }

    public function render()
    {
        $payload = $this->pipe();

        return $payload->svg->toXMLString();
    }

    private function shapes()
    {
        return $this->makeInstances(__DIR__ . '/Processors/Shapes');
    }

    private function processors()
    {
        $processors = [
            ...$this->shapes(),
            ...$this->makeInstances(__DIR__ . '/Processors')
        ];

        usort($processors, function (BaseProcessor $a, BaseProcessor $b) {
            return $a->sortOrder() - $b->sortOrder();
        });

        return $processors;
    }

    /**
     * @return ProcessorPayload
     */
    private function pipe()
    {
        $payload = new ProcessorPayload();

        $payload->qrcode = $this->qrcode;

        $pipeline = new Pipeline(app());

        $pipeline->send($payload)->through($this->processors());

        $payload = $pipeline->thenReturn();

        return $payload;
    }
}
