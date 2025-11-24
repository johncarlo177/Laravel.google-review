<?php

namespace App\Support\QRCodeProcessors;

use App\Support\SvgModuleRenderer\BaseRenderer;
use Illuminate\Support\Facades\Log;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Structures\SVGMask;
use SVG\Nodes\Structures\SVGSymbol;
use SVG\SVG;

class DummyDataProcessor extends BaseProcessor
{

    protected $dummySegmentSymbol;

    protected $dummyQrCodeSymbol;

    protected $dummyMask;

    protected function shouldProcess(): bool
    {
        $svgSize = $this->getSvgViewBoxSize();

        $qrcodeSize = $this->output->size;

        return $svgSize > $qrcodeSize;
    }

    protected function process()
    {
        $this->doc = $this->svg->getDocument();

        $this->dummySegmentSymbol = $this->makeDummySegmentSymbol();

        $this->dummyQrCodeSymbol = $this->makeDummyQRCodeSymbol();

        $this->dummyMask = $this->makeDummyMask();

        $this->doc->addChild($this->dummySegmentSymbol);

        $this->doc->addChild($this->dummyQrCodeSymbol);

        $this->doc->addChild($this->dummyMask);

        $origin = $this->getViewBoxStart();

        $dummyRect = new SVGRect(
            $origin,
            $origin,
            $this->getSvgViewBoxSize(),
            $this->getSvgViewBoxSize()
        );

        $dummyRect->setAttribute(
            'mask',
            'url(#' . $this->dummyMask->getAttribute('id') . ')'
        );

        $dummyRect->setAttribute('class', 'dark type-data');

        $container = $this->doc->getElementsByClassName('foreground-0')[0];

        $this->addChild($container, $dummyRect);
    }

    protected function makeDummyMask()
    {
        $mask = new SVGMask();

        $mask->setAttribute('id', 'dummy-mask');

        /**
         * 1, 2, 3
         * 8, -, 4
         * 7, 6, 5
         */

        // l = qrcode length

        // 1 => -l, -l
        // 2 =>  0, -l
        // 3 =>  l, -l
        // 4 =>  l,  0
        // 5 =>  l,  l
        // 6 =>  0,  l
        // 7 => -l,  l
        // 8 => -l,  0

        $l = $this->output->size;

        $neighbors = [
            1 => [-$l, -$l],
            2 => [0, -$l],
            3 => [$l,  -$l],
            4 => [$l,   0],
            5 => [$l,  $l],
            6 => [0,  $l],
            7 => [-$l,  $l],
            8 => [-$l,   0]
        ];

        for ($i = 1; $i <= 8; $i++) {
            // if ($i > 1) continue;

            $x = $neighbors[$i][0];
            $y = $neighbors[$i][1];

            $used = $this->use(
                $this->dummyQrCodeSymbol,
                $x,
                $y
            );


            $used->setAttribute('fill', 'white');

            $mask->addChild($used);
        }

        // building second fold

        $cords = [
            [-$l * 2, $l * 2],
            [-$l, $l * 2],
            [0, $l * 2],
            [$l, $l * 2],
            [$l * 2, $l * 2],
            [$l * 2, $l],
            [$l * 2, 0],
            [$l * 2, -$l],
            [$l * 2, -2 * $l],
            [$l, -2 * $l],
            [0, -2 * $l],
            [-$l, -2 * $l],
            [-2 * $l, -2 * $l],
            [-2 * $l, -$l],
            [-2 * $l, 0],
            [-2 * $l, $l],

            // 2 rows to the bottom
            [-2 * $l, 3 * $l],
            [-$l, 3 * $l],
            [0, 3 * $l],
            [$l, 3 * $l],
            [$l * 2, 3 * $l],

            [-2 * $l, 4 * $l],
            [-$l, 4 * $l],
            [0, 4 * $l],
            [$l, 4 * $l],
            [$l * 2, 4 * $l],

            // 1 row to the right
            [3 * $l, -2 * $l],
            [3 * $l, -$l],
            [3 * $l, 0],
            [3 * $l, $l],
            [3 * $l, 2 * $l],
            [3 * $l, 3 * $l],

            // 1 row to the left

            [-3 * $l, -2 * $l],
            [-3 * $l, -$l],
            [-3 * $l, 0],
            [-3 * $l, $l],
            [-3 * $l, 2 * $l],
            [-3 * $l, 3 * $l],

        ];

        foreach ($cords as $point) {
            $used = $this->use(
                $this->dummyQrCodeSymbol,
                $point[0],
                $point[1]
            );

            $used->setAttribute('fill', 'white');

            $mask->addChild($used);
        }

        // make empty area around QR code
        // margin in points
        $m = $this->marginWidth();

        $rect = new SVGRect(-$m / 2, -$m / 2, $l + $m, $l + $m);
        $rect->setStyle('fill', 'none');
        $rect->setStyle('stroke', '#000000');
        $rect->setStyle('stroke-width', $m);

        $mask->addChild($rect);

        return $mask;
    }

    protected function marginWidth()
    {
        return  BaseRenderer::$qrcodeScale;
    }

    protected function dummyLength()
    {
        return $this->output->size / 3;
    }

    protected function makeDarkMask()
    {
        $this->doc = $this->svg->getDocument();

        $darkPaths = $this->doc->getElementsByClassName('dark');

        $mask = new SVGMask();

        $mask->setAttribute('id', 'dark-mask');

        foreach ($darkPaths as $path) {
            $class = $path->getAttribute('class');
            $class = preg_replace('/dark /', '', $class);
            $path->setAttribute('class', $class);
            $path->setAttribute('fill', 'white');
            $mask->addChild($path);
        }

        return $mask;
    }

    protected function makeDummySegmentSymbol()
    {
        $dummySymbol = new SVGSymbol();

        $dummySymbol->setAttribute('id', 'dummy-segment');

        $dummyDataRect = new SVGRect(
            $this->dummyLength(),
            0,
            $this->dummyLength(),
            $this->output->size
        );

        $dummySymbol->addChild($dummyDataRect);

        $dummyDataRect->setAttribute('mask', sprintf('url(#%s)', DarkMaskProcessor::$darkMaskId));

        return $dummySymbol;
    }

    protected function makeDummyQRCodeSymbol()
    {
        $numberOfSegments = round($this->output->size / $this->dummyLength());

        $dummyQrCodeSymbol = new SVGSymbol();

        $dummyQrCodeSymbol->setAttribute('id', 'dummy-qrcode');

        for ($i = 0; $i < $numberOfSegments; $i++) {
            $dummyQrCodeSymbol->addChild(
                $this->use('dummy-segment', ($i - 1) * $this->dummyLength(), 0)
            );
        }

        return $dummyQrCodeSymbol;
    }
}
