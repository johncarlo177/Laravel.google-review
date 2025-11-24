<?php

namespace App\Support\CompatibleSVG\Processors\Shapes;

use App\Support\CompatibleSVG\Processors\BaseProcessor;
use App\Support\CompatibleSVG\Processors\Shapes\Interfaces\OverridesOutlinedShape;
use App\Support\System\Traits\ClassListLoader;
use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGGroup;
use SVG\SVG;

class OutlinedShape extends BaseProcessor
{
    use ClassListLoader;

    protected SVG $shape;

    public const ID_MASK = 'outlined-shape';

    public const ID_FRAME_NODE = 'frame-node';

    public static function getShapes()
    {
        $files = glob(__DIR__ . '/svg/*.svg');

        $files = array_filter($files, function ($file) {
            return !preg_match('/scaffold/', $file);
        });

        return array_map(function ($file) {
            $name = basename($file);

            $name = str_replace('.svg', '', $name);

            return $name;
        }, $files);
    }

    public function sortOrder()
    {
        return 10;
    }

    protected function shouldProcess()
    {
        return !empty($this->qrcodeShapeSlug()) &&
            $this->svgFileExists() && $this->isNotOverriden();
    }

    protected function isNotOverriden()
    {
        $overrides = collect($this->makeInstances(__DIR__))
            ->filter(function ($obj) {
                return $obj instanceof OverridesOutlinedShape;
            })->filter(function (OverridesOutlinedShape $obj) {
                return $obj::slug() === $this->qrcodeShapeSlug();
            });

        return $overrides->isEmpty();
    }

    protected function qrcodeShapeSlug()
    {
        return $this->qrcode()->design->shape;
    }

    protected function process()
    {
        $this->initShape();

        $qrcode = $this->addQRCodeToShape();

        $dummy = $this->generateDummyData();

        $group = new SVGGroup();

        $group->addChild($dummy);

        $group->addChild($qrcode);

        $this->s->doc($this->shape)->addChild($group);

        if ($this->shouldApplyMask()) {
            $this->s->mask(
                $group,
                $this->dummyDataAreaPath(),
                $this->shape,
                static::ID_MASK
            );
        }

        $this->renderFrame();

        $this->removeQRCodeRect();

        $this->payload->svg = $this->shape;
    }

    protected function shouldApplyMask()
    {
        return true;
    }

    protected function renderFrame()
    {
        $frame = $this->getFrameNode();

        if (!$frame)
            return $this->useMaskPathAsFrame();

        $parent = $frame->getParent();

        $frame->setStyle('stroke', $this->getFrameColor());

        // Ensure frame is rendered at the end of the document
        $parent->removeChild($frame);

        $parent->addChild($frame);
    }

    protected function getFrameNode()
    {
        return $this->s->doc($this->shape)->getElementById(static::ID_FRAME_NODE);
    }

    protected function removeQRCodeRect()
    {
        $this->getQRCodeRect()->getParent()->removeChild($this->getQRCodeRect());
    }

    protected function useMaskPathAsFrame()
    {
        $path = $this->s->clonePath($this->dummyDataAreaPath());

        $path->setStyle('fill', 'none');

        $path->setStyle('stroke', $this->getFrameColor());

        $path->setAttribute('id', static::ID_FRAME_NODE);

        $this->s->doc($this->shape)->addChild($path);

        return $path;
    }

    protected function getFrameColor()
    {
        return $this->qrcode()->design->frameColor;
    }

    private function generateDummyData()
    {
        $width = $this->s->width($this->shape) / $this->getScale();
        $height = $this->s->height($this->shape) / $this->getScale();

        $d = '';

        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {
                if (random_int(0, 100) % 2)
                    $d .= sprintf(' M%s,%s h1 v1 h-1Z', $i, $j);
            }
        }

        $path = new SVGPath($d);

        $path->setStyle('fill', '#000000');

        $path->setAttribute('class', 'dummy-data');

        $group = new SVGGroup();

        $group->addChild($path);

        $group->setAttribute(
            'transform',
            sprintf('scale(%s)', $this->getScale())
        );

        $this->s->doc($this->shape)->addChild($group, 0);

        return $group;
    }

    private function addQRCodeToShape()
    {
        $qrcodeDarkPath = $this->doc()->getElementsByClassName('dark')[0];

        $background = $this->makeQRCodeBackground();

        $group = new SVGGroup();

        $group->addChild($qrcodeDarkPath);

        $group->setAttribute('transform', sprintf('scale(%s)', $this->getScale()));

        $group2 = new SVGGroup();

        $group2->addChild($background);

        $group2->addChild($group);

        list($x, $y) = $this->getQRCodeRectCoordinates();

        $group2->setAttribute(
            'transform',
            sprintf(
                'translate(%s, %s)',
                $x,
                $y
            )
        );

        $this->s->doc($this->shape)->addChild($group2);

        return $group2;
    }

    protected function getScale()
    {
        list($x, $y, $width, $height) = $this->getQRCodeRectCoordinates(); // shape coor

        $qrcodeWidth = $this->s->width($this->svg());

        $scale = $width / $qrcodeWidth;

        return $scale;
    }

    protected function getQRCodeRect()
    {
        return $this->s->doc($this->shape)->getElementById('qrcode-rect');
    }

    protected function getQRCodeRectCoordinates()
    {

        return [
            $this->getQRCodeRect()->getAttribute('x'),
            $this->getQRCodeRect()->getAttribute('y'),
            $this->getQRCodeRect()->getAttribute('width'),
            $this->getQRCodeRect()->getAttribute('height'),
        ];
    }

    protected function qrcodeBackgroundMargin()
    {
        return 1;
    }

    private function makeQRCodeBackground()
    {
        list($x, $y, $width, $height) = $this->getQRCodeRectCoordinates();

        $margin = $this->qrcodeBackgroundMargin() * $this->getScale();

        $rect = new SVGRect(
            -$margin,
            -$margin,
            $width + $margin * 2,
            $height + $margin * 2
        );

        $rect->setAttribute('id', 'qrcode-background');

        $rect->setAttribute('fill', '#ffffff');

        return $rect;
    }

    protected function dummyDataAreaPath()
    {
        $path = $this->s->doc($this->shape)->getElementById('dummy-data-area');

        return $path;
    }

    private function svgFilePath()
    {
        return sprintf('%s/svg/%s.svg', __DIR__, $this->qrcodeShapeSlug());
    }

    private function svgFileExists()
    {
        return file_exists($this->svgFilePath());
    }

    private function initShape()
    {
        $string = file_get_contents($this->svgFilePath());

        $this->shape = SVG::fromString($string);

        $this->s->syncDimensionsWithViewBox($this->shape);
    }
}
