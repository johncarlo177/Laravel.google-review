<?php

namespace App\Support\CompatibleSVG\Processors;

use App\Support\CompatibleSVG\ProcessorPayload;
use App\Support\CompatibleSVG\SVGHelper;
use Closure;

abstract class BaseProcessor
{
    protected ProcessorPayload $payload;

    protected SVGHelper $s;

    abstract protected function process();

    private function init()
    {
        $this->s = new SVGHelper;
    }

    public function sortOrder()
    {
        return 1000;
    }

    protected function shouldProcess()
    {
        return true;
    }

    protected function svg()
    {
        return $this->payload->svg;
    }

    protected function doc()
    {
        return $this->svg()->getDocument();
    }

    protected function qrcode()
    {
        return $this->payload->qrcode;
    }

    public function handle(ProcessorPayload $payload, Closure $next)
    {
        $this->init();

        $this->payload = $payload;

        if ($this->shouldProcess()) {
            $this->process();
        }

        return $next($payload);
    }
}
