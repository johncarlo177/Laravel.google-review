<?php

namespace App\Support\QRCodeProcessors\ShapeProcessors;

use App\Support\QRCodeProcessors\BaseProcessor;
use App\Interfaces\QRCodeProcessor;
use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Structures\SVGMask;
use SVG\Nodes\Structures\SVGStyle;
use SVG\Nodes\Structures\SVGSymbol;

abstract class BaseShapeProcessor extends BaseProcessor
{
    public static $shapeId = '';

    protected $svgSymbol;

    protected function shouldPostProcess()
    {
        return $this->shouldProcess();
    }

    protected function process()
    {
        $doc = $this->svg->getDocument();

        $doc->setAttribute('viewBox', $this->modifiedViewBox());

        $defs = $doc->getElementsByTagName('defs')[0];

        $this->svgSymbol = $this->makeSymbol();

        $this->addChild($defs, $this->svgSymbol);

        $style = new SVGStyle($this->renderStyles());

        $this->addChild($defs, $style);

        $this->makeShapeMask();

        $this->makeFrame();
    }

    protected function modifiedViewBox()
    {
        return sprintf(
            '%1$s %1$s %2$s %2$s',
            -$this->output->size,
            $this->output->size * 3
        );
    }

    protected function makeSymbol()
    {
        $symbol = new SVGSymbol();

        $symbol->setAttribute('id', $this::symbolId());

        $symbol->setAttribute('viewBox', $this->symbolViewBox());

        $path = new SVGPath($this->symbolPath());

        $path->setAttribute('transform', $this->symbolTransform());

        $symbol->addChild($path);

        $this->extendSymbol($symbol);

        return $symbol;
    }

    protected function extendSymbol(SVGSymbol $symbol) {}

    public function symbolViewBox()
    {
        return '0 0 24 24';
    }

    abstract public function symbolPath();

    public function symbolTransform()
    {
        return '';
    }

    protected static function symbolId()
    {
        return 'symbol-' . static::$shapeId;
    }

    protected function makeShapeMask()
    {
        $mask = new SVGMask();

        $pos = $this->getViewBoxStart();

        $usedSymbol = $this->use($this->svgSymbol, $pos, $pos);

        $usedSymbol->setAttribute('fill', 'white');

        $mask->setAttribute('id', $this::maskId());

        $mask->addChild($usedSymbol);

        $this->doc->addChild($mask);
    }

    protected static function maskId()
    {
        return 'mask-' . static::$shapeId;
    }

    protected static function frameId()
    {
        return 'frame-' . static::$shapeId;
    }

    protected function makeFrame()
    {
        $frame = $this->frameNode();

        $frame->setAttribute('id', $this::frameId());

        $strokeWidth = $this->frameStrokeWidth();

        $frame->setAttribute('stroke-width', $strokeWidth);

        $frame->setAttribute('fill', 'none');

        $frame->setAttribute('stroke', $this->qrcode->design->frameColor);

        $this->doc->addChild($frame);
    }

    protected function frameNode()
    {
        return $this->use(
            $this->svgSymbol,
            $this->getViewBoxStart(),
            $this->getViewBoxStart(),
        );
    }

    protected function frameStrokeWidth()
    {
        return $this->getSvgViewBoxSize() / $this->getSvgViewBoxSize(
            $this->svgSymbol
        ) / 150;
    }

    protected function renderStyles()
    {
        return sprintf(
            '.foreground-0 {
                mask: url(#%s);
            } %s',
            $this::maskId(),
            $this->extendStyles()
        );
    }

    protected function extendStyles()
    {
        return '';
    }

    protected function postProcess()
    {
        $doc = $this->svg->getDocument();

        $frame = $doc->getElementById($this->frameId());

        $doc->removeChild($frame);

        $doc->addChild($frame);
    }

    protected function shouldProcess(): bool
    {
        return !empty($this->qrcode->design->shape) &&
            $this->qrcode->design->shape === $this::$shapeId;
    }
}
