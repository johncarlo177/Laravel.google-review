<?php

namespace App\Support\QRCodeProcessors;

use App\Exceptions\InvalidOrderException;
use App\Interfaces\QRCodeProcessor;
use App\Models\QRCode;
use App\Support\QRCodeOutput;
use App\Support\System\Traits\WriteLogs;
use Closure;
use SVG\Nodes\Structures\SVGDocumentFragment;
use SVG\SVG;

abstract class BaseProcessor implements QRCodeProcessor
{
    use WriteLogs;

    use Traits\SVGOperations;

    protected QRCodeOutput $output;

    protected QRCode $qrcode;

    protected string $data;

    protected string $svgString;

    protected SVG $svg;

    protected SVGDocumentFragment $doc;

    public function handle(QRCodeOutput $output, Closure $next)
    {
        $this->logDebug('Handling ...');

        $this->output = $output;

        $this->qrcode = $output->qrcode;

        $this->data = $output->data;

        $this->svgString = $output->svgString;

        if (isset($output->svg)) {
            $this->svg = $output->svg;
            $this->doc = $this->svg->getDocument();
        }

        if ($output->round === 0) {
            $this->validateSvg();
        }

        if ($output->round === 0 && $this->shouldProcess()) {
            $this->processTraits();
            $this->process();

            $this->logDebug('Proceessing completed ...');
        }

        if ($output->round > 0 && $this->shouldPostProcess()) {
            $this->postProcess();

            $this->logDebug('Post processing completed ...');
        }

        $this->logDebug('Completed ...');

        return $next($output);
    }

    protected abstract function process();

    private function processTraits()
    {
        $methods = get_class_methods($this);

        $currentMethod = __FUNCTION__;

        $traitProcessors = array_filter(
            $methods,
            fn($method) => preg_match('/process[A-Za-z]+/', $method)
                && $method !== $currentMethod
        );

        foreach ($traitProcessors as $processor) {
            call_user_func([$this, $processor]);
        }
    }

    protected function shouldPostProcess()
    {
        return false;
    }

    protected function postProcess() {}

    protected function validateSvg()
    {
        if (empty($this->output->svgString)) {
            throw new InvalidOrderException(static::class . ' expects svg output to exists!');
        }
    }

    protected abstract function shouldProcess(): bool;

    protected function addChild($node, $child, $index = null)
    {
        return call_user_func([$node, 'addChild'], $child, $index);
    }
}
