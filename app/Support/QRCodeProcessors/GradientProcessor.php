<?php

namespace App\Support\QRCodeProcessors;

use Illuminate\Support\Facades\Log;
use stdClass;
use SVG\Nodes\Presentation\SVGLinearGradient;
use SVG\Nodes\Presentation\SVGRadialGradient;
use SVG\Nodes\Presentation\SVGStop;
use SVG\Nodes\Shapes\SVGLine;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGMask;
use SVG\Nodes\Structures\SVGStyle;

class GradientProcessor extends BaseProcessor
{
    use Traits\JoinsForegroundMasks;

    private $gradient;

    protected static $joinedMaskId = 'mask-joined-for-gradient';

    public static $gradientId = 'gradient-data-fill';

    public static $transformResetMaskId = 'mask-transform-reset-by-gradient';

    protected function shouldProcess(): bool
    {
        return $this->qrcode->design->fillType === 'gradient';
    }

    protected function process()
    {
        $this->gradient = $this->buildGradientObject();

        $this->makeGradient();

        $this->makeTransformResetMask();
    }

    protected function toRadians($degrees)
    {
        return $degrees / 180 * pi();
    }

    // Translated from https://codepen.io/NV/pen/AwYpxb
    protected function angleToPoints($angle)
    {
        $segment = floor($angle / pi() * 2) + 2;

        $diagnoal = (1 / 2 * $segment + 1 / 4) * pi();

        $op = cos(abs($diagnoal - $angle)) * sqrt(2);

        $x = $op * cos($angle);

        $y = $op * sin($angle);

        return [
            'x1' => $x < 0 ? 1 : 0,
            'y1' => $y < 0 ? 1 : 0,
            'x2' => $x >= 0 ? $x : $x + 1,
            'y2' => $y >= 0 ? $y : $y + 1
        ];
    }

    public static function defaultGradient()
    {
        return [
            'type' => 'LINEAR',
            'angle' => 45,
            'colors' => [
                [
                    'color' => '#000000',
                    'stop' => 0,
                    'opacity' => 1,
                ],
                [
                    'color' => '#808080',
                    'stop' => 33,
                    'opacity' => 1,
                ],
                [
                    'color' => '#000000',
                    'stop' => 70,
                    'opacity' => 1,
                ],
                [
                    'color' => '#808080',
                    'stop' => 100,
                    'opacity' => 1,
                ],
            ]
        ];
    }

    private function buildGradientObject()
    {
        if (is_string($this->qrcode->design->gradientFill)) {
            $param = json_decode($this->qrcode->design->gradientFill, true);
        } else {
            $param = $this->qrcode->design->gradientFill;
        }

        $obj = (object) array_merge(
            static::defaultGradient(),
            (array) $param
        );

        $obj = json_decode(json_encode($obj));

        foreach ($obj->colors as $color) {
            $color->stop = $color->stop ?? 0;
            $color->opacity = $color->opacity ?? 1;
            $color->color = $color->color ?? '#000000';
        }

        return $obj;
    }

    protected function makeGradient()
    {
        $gradientType = $this->gradient->type;

        $gradient = new SVGLinearGradient();

        if ($gradientType === 'RADIAL') {
            $gradient = new SVGRadialGradient();
        }

        if (empty($this->gradient->angle)) {
            $this->gradient->angle = 0;
        }

        $angle = ($this->gradient->angle % 360) - 180;

        $points = $this->angleToPoints($this->toRadians($angle));

        foreach ($points as $var => $value) {
            $$var = ($value * 100) . '%';
        }

        $gradient->setAttribute('x1', $x1);
        $gradient->setAttribute('y1', $y1);
        $gradient->setAttribute('x2', $x2);
        $gradient->setAttribute('y2', $y2);

        usort($this->gradient->colors, fn ($c1, $c2) => $c1->stop - $c2->stop);

        foreach ($this->gradient->colors as $color) {

            $stop = new SVGStop();
            $stop->setAttribute('offset', $color->stop . '%');
            $stop->setAttribute('stop-color', $color->color);
            $stop->setAttribute('opacity', $color->opacity);
            $gradient->addChild($stop);
        }

        $gradient->setAttribute('id', $this::$gradientId);

        $this->doc->addChild($gradient);
    }

    protected function makeTransformResetMask()
    {
        $foreground3 = $this->doc->getElementsByClassName('foreground-3')[0];

        $foreground3->setAttribute('fill', 'white');

        $mask = new SVGMask();

        $mask->setAttribute('id', $this::$transformResetMaskId);

        $parent = $foreground3->getParent();

        $mask->addChild($foreground3);

        $parent->addChild($mask);

        $x = $this->getViewBoxStart();
        $y = $x;
        $l = $this->getViewBoxWidth();

        $rect = new SVGRect($x, $y, $l, $l);

        $rect->setAttribute('mask', sprintf('url(#%s)', $this::$transformResetMaskId));

        $parent->addChild($rect);

        $parent->setAttribute('fill', sprintf('url(#%s)', $this::$gradientId));
    }
}
