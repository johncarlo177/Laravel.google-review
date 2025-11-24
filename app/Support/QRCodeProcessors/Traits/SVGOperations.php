<?php

namespace App\Support\QRCodeProcessors\Traits;

use InvalidArgumentException;
use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Structures\SVGSymbol;
use SVG\Nodes\Structures\SVGUse;
use SVG\Nodes\SVGNode;

trait SVGOperations
{

    private function getViewBox($svgNode = null)
    {
        if ($svgNode === null)
            $svgNode = $this->svg->getDocument();

        $viewBox = explode(' ', $svgNode->getAttribute('viewBox'));

        return $viewBox;
    }

    protected function getSvgViewBoxSize($svgNode = null): int
    {
        $viewBox = $this->getViewBox($svgNode);

        $x = $viewBox[0];

        $w = $viewBox[2];

        $y = $viewBox[1];

        $h = $viewBox[3];

        // View box should be square
        return $w;
    }

    protected function getViewBoxWidth($svgNode = null): int
    {
        return $this->getViewBox($svgNode)[2];
    }

    protected function getViewBoxHeight($svgNode = null): int
    {
        return $this->getViewBox($svgNode)[3];
    }

    protected function getViewBoxOrigin($svgNode = null)
    {
        list($x, $y) = $this->getViewBox($svgNode);

        return [$x, $y];
    }

    protected function getViewBoxOriginX($svgNode = null)
    {
        return $this->getViewBoxOrigin($svgNode)[0];
    }

    protected function getViewBoxOriginY($svgNode = null)
    {
        return $this->getViewBoxOrigin($svgNode)[1];
    }

    protected function inlineImageHref($type, $string)
    {
        return 'data:' . $type .
            ';base64,' . base64_encode($string);
    }

    protected function inlineImagickHref($imagick)
    {
        return $this->inlineImageHref($imagick->getImageMimeType(), $imagick->__toString());
    }

    protected function getViewBoxStart()
    {
        $doc = $this->svg->getDocument();

        $viewBox = explode(' ', $doc->getAttribute('viewBox'));

        $x = $viewBox[0];

        return $x;
    }

    protected function use($symbol, $x, $y, $width = null, $height = null)
    {
        $id = is_string($symbol) ? $symbol : ($symbol instanceof SVGNode ? $symbol->getAttribute('id') : null);

        if (!$id) {
            throw new InvalidArgumentException('Symbol not found');
        }

        $use = new SVGUse();

        $use->setAttribute('x', $x);

        $use->setAttribute('y', $y);

        $use->setAttribute('href', '#' . $id);

        if ($width !== null) {
            $use->setAttribute('width', $width);
        }

        if ($height !== null) {
            $use->setAttribute('height', $height);
        }

        return $use;
    }

    protected function makeLayeredGroups($count, $class = 'layer')
    {
        $layers = [];

        $layers = array_map(function ($i) use ($class) {
            $layer = new SVGGroup();

            $layer->setAttribute('class', sprintf('%s-%s', $class, $i));

            return $layer;
        }, range(0, $count));

        for ($i = count($layers) - 1; $i >= 0; $i--) {

            $layer = $layers[$i];

            if ($i > 0) {
                $layer->addChild($layers[$i - 1]);
            }
        }

        $base = $layers[count($layers) - 1];

        $this->doc->addChild($base);

        return $base;
    }

    protected function makeSinglePathUsedSymbol($symbolId, $viewBox, $pathCommands, $useNodeId)
    {
        $symbol = new SVGSymbol();

        $symbol->setAttribute('id', $symbolId);

        $symbol->setAttribute('viewBox', $viewBox);

        $path = new SVGPath($pathCommands);

        $symbol->addChild($path);

        $this->doc->addChild($symbol);

        $node = $this->use($symbol, $this->getViewBoxStart(), $this->getViewBoxStart(), '100%', '100%');

        $node->setAttribute('id', $useNodeId);

        return $node;
    }

    protected function makeMultiplePathUsedSymbol($symbolId, $viewBox, array $pathCommands, $useNodeId)
    {
        $symbol = new SVGSymbol();

        $symbol->setAttribute('id', $symbolId);

        $symbol->setAttribute('viewBox', $viewBox);

        foreach ($pathCommands as $d) {
            $path = new SVGPath($d);

            $symbol->addChild($path);
        }

        $this->doc->addChild($symbol);

        $node = $this->use($symbol, $this->getViewBoxStart(), $this->getViewBoxStart(), '100%', '100%');

        $node->setAttribute('id', $useNodeId);

        return $node;
    }

    protected function toRadians($degrees)
    {
        return $degrees / 180 * pi();
    }

    protected function removeChild(SVGNode $parent, $child)
    {
        return call_user_func([$parent, 'removeChild'], $child);
    }
}
