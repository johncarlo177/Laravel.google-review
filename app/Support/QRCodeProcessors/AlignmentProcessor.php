<?php

namespace App\Support\QRCodeProcessors;

use App\Support\SvgModuleRenderer\BaseRenderer;
use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Structures\SVGSymbol;

class AlignmentProcessor extends BaseProcessor
{
    protected $unitSymbol;

    protected $symbol;

    protected function shouldProcess(): bool
    {
        return !empty($this->getUnitPaths()) &&
            $this->doc->getElementById($this->symbolId()); // qr version 1 does not have alignment
    }
    protected function symbolId()
    {
        return 'symbol-alignment';
    }

    protected function getUnitPaths()
    {
        $unitSymbolIds = [
            'symbol-finder-unit',
            'symbol-finder-dot-unit',
        ];

        $paths = array_reduce(
            $unitSymbolIds,
            function ($paths, $id) {
                $symbol = $this->doc->getElementById($id);

                if (!empty($symbol)) {
                    $paths[] = $symbol->getElementsByTagName('path')[0];
                }

                return $paths;
            },
            []
        );

        return $paths;
    }

    protected function unitSymbolId()
    {
        return 'symbol-alignment-unit';
    }

    protected function getUnitViewbox()
    {
        return '0 0 700 700';
    }

    protected function renderUnitSymbol()
    {
        $paths = $this->getUnitPaths();

        $this->unitSymbol = new SVGSymbol();

        $this->unitSymbol->setAttribute('id', $this->unitSymbolId());

        $this->unitSymbol->setAttribute('viewBox', $this->getUnitViewbox());

        foreach ($paths as $path) {

            if ($path->getParent()->getAttribute('id') === 'symbol-finder-dot-unit') {
                $clone = $this->cloneDot($path);
            } else {
                $clone = $this->cloneFinder($path);
            }

            $this->unitSymbol->addChild($clone);
        }

        $this->doc->addChild($this->unitSymbol);
    }

    protected function clonePath($path, $scale, &$clonedPath = null)
    {
        $clone = new SVGPath($path->getAttribute('d'));

        $clone->setAttribute('transform', $path->getAttribute('transform'));

        $group = new SVGGroup();

        $group->addChild($clone);

        $group->setAttribute('transform', "scale($scale)");

        $group->setAttribute('transform-origin', '350 350');

        $clonedPath = $clone;

        return $group;
    }

    protected function cloneFinder($path)
    {
        $clone = $this->clonePath($path, 0.9, $clonedPath);

        $clonedPath->setAttribute('stroke', 'white');

        $clonedPath->setAttribute('stroke-width', 70);

        $clone->setAttribute('class', 'alignment-outer');

        return $clone;
    }

    protected function cloneDot($path)
    {
        $clone = $this->clonePath($path, 0.4);

        return $clone;
    }

    protected function renderSymbol()
    {
        $this->symbol = $this->doc->getElementById($this->symbolId());

        $this->removeChild($this->symbol, 0);

        $coords = BaseRenderer::$alignmentModules;

        $scale = $this->getSvgViewBoxSize() /  BaseRenderer::$qrModuleCount;

        $alignmentLength = 5 * $scale;

        $margin = 0.5 * $scale * 0;

        foreach ($coords as $i => $point) {
            $group = new SVGGroup();

            list($x, $y) = $point;

            $middleMargin = 0;

            $dx = abs($x - BaseRenderer::$qrModuleCount / 2);

            $dy = abs($y - BaseRenderer::$qrModuleCount / 2);

            if ($dx < 3 && $dy < 3) {
                $middleMargin = $scale * 2 * 0;
            }

            $group->setAttribute(
                'transform',
                sprintf(
                    'translate(%1$s, %2$s)',
                    $x * $scale + $margin + $middleMargin,
                    $y * $scale + $margin + $middleMargin
                )
            );

            $originGroup = new SVGGroup();

            $used = $this->use($this->unitSymbol, 0, 0);

            $used->setAttribute('width', $alignmentLength);

            $used->setAttribute('height', $alignmentLength);

            $used->setAttribute('fill', 'white');

            $used->setAttribute(
                'transform-origin',
                sprintf('%s %s', $alignmentLength / 2, $alignmentLength / 2)
            );

            $originGroup->addChild($used);

            $group->addChild($originGroup);

            $this->addChild($this->symbol, $group);
        }
    }

    protected function process()
    {
        $this->renderUnitSymbol();
        $this->renderSymbol();
    }
}
