<?php

namespace App\Support\QRCodeProcessors\FinderProcessors;

use App\Support\QRCodeProcessors\BaseProcessor;
use App\Support\SvgModuleRenderer\BaseRenderer;
use Illuminate\Support\Facades\Log;
use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Structures\SVGSymbol;

abstract class BaseFinderProcessor extends BaseProcessor
{
    protected $symbol;

    protected $unitSymbol;

    protected $mask;

    protected $id;

    protected $shouldFlip = false;

    protected function shouldProcess(): bool
    {
        return $this->qrcode->design->finder == $this->id;
    }

    protected function getFinderViewboxSize()
    {
        return '0 0 700 700';
    }

    protected function renderSymbolPath()
    {
        $d = $this->pathCommands();

        $path = new SVGPath($d);

        $path->setAttribute(
            'transform',
            sprintf('scale(%s)', $this->pathScale())
        );

        $this->addChild($this->unitSymbol, $path);
    }

    protected function pathScale()
    {
        return 1;
    }

    protected function symbolSvgId()
    {
        return 'symbol-finder';
    }

    protected function unitSymbolSvgId()
    {
        return $this->symbolSvgId() . '-unit';
    }

    protected function renderUnitSymbol()
    {
        $this->unitSymbol = new SVGSymbol();

        $this->unitSymbol->setAttribute('id', $this->unitSymbolSvgId());

        $this->unitSymbol->setAttribute('viewBox', $this->getFinderViewboxSize());

        $this->renderSymbolPath();

        $this->doc->addChild($this->unitSymbol);
    }

    protected function renderFinderSymbol()
    {
        $this->symbol = $this->doc->getElementById($this->symbolSvgId());

        // Remove default finder modules
        $this->removeChild($this->symbol, 0);

        // QRCode length
        $l = $this->getSvgViewBoxSize();

        $coords = [
            [0, 0],
            [$l, 0],
            [0, $l],
        ];

        $symbolTranslate = [
            [0, 0],
            [-1, 0],
            [0, -1],
        ];

        $flip = $this->flip();

        // Finder larger x is 6. We add 1 to it to get the length.

        $finderSymbolLength = 7 * ($this->getSvgViewBoxSize() /  BaseRenderer::$qrModuleCount);

        $marginArray = [
            [1, 1],
            [-1, 1],
            [1, -1]
        ];


        $margin = 0.5 * $this->getSvgViewBoxSize() / BaseRenderer::$qrModuleCount;

        $margin = 0;

        // Disable finder dot margins for the default finder
        if (
            $this->qrcode->design->finder === 'default'
            || $this->qrcode->design->finderDot === 'default'
        ) {
            $margin = 0;
        }

        foreach ($coords as $i => $point) {
            $group = new SVGGroup();

            $group->setAttribute(
                'transform',
                sprintf(
                    'translate(%1$s, %2$s)',
                    $point[0],
                    $point[1]
                )
            );

            $st = $symbolTranslate[$i];

            // Margin item
            $mi = $marginArray[$i];

            // Margin on x
            $mx = $mi[0] * $margin;

            // Margin on y
            $my = $mi[1] * $margin;

            $originGroup = new SVGGroup();

            $originGroup->setAttribute(
                'transform',
                sprintf(
                    'translate(%s, %s)',
                    $st[0] * $finderSymbolLength + $mx,
                    $st[1] * $finderSymbolLength + $my,
                )
            );

            $used = $this->use($this->unitSymbol, 0, 0);

            $used->setAttribute('width', $finderSymbolLength);

            $used->setAttribute('height', $finderSymbolLength);

            $used->setAttribute('fill', 'white');

            $fx = $flip[$i][0] === 1 ? -1 : 1;

            $fy = $flip[$i][1] === 1 ? -1 : 1;

            $used->setAttribute(
                'transform',
                sprintf(
                    'scale(%s, %s)',
                    $fx,
                    $fy
                )
            );

            $used->setAttribute(
                'transform-origin',
                sprintf('%s %s', $finderSymbolLength / 2, $finderSymbolLength / 2)
            );

            $originGroup->addChild($used);

            $group->addChild($originGroup);

            $this->addChild($this->symbol, $group);
        }
    }

    protected function maskSvgId()
    {
        return 'mask-finder';
    }

    protected function renderFinderMask()
    {
        $this->mask = $this->doc->getElementById($this->maskSvgId());

        $this->removeChild($this->mask, 0);

        $used = $this->use(
            $this->symbol,
            0,
            0
        );

        $this->addChild($this->mask, $used);
    }

    protected function process()
    {
        $points = array_reduce(
            BaseRenderer::$alignmentModules,
            function ($carry, $item) {
                $carry .= sprintf("x = %s, y = %s \n", ...$item);
                return $carry;
            },
            ''
        );

        $this->renderUnitSymbol();

        $this->renderFinderSymbol();

        $this->renderFinderMask();
    }

    protected abstract function pathCommands();

    protected function flip()
    {
        if ($this->shouldFlip) {
            return [
                [0, 0],
                [1, 0],
                [0, 1]
            ];
        }

        return array_fill(0, 3, [0, 0]);
    }
}
