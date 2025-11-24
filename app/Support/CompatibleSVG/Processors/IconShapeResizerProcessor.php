<?php

namespace App\Support\CompatibleSVG\Processors;

use App\Support\CompatibleSVG\Processors\Shapes\OutlinedShape;
use App\Support\System\Traits\WriteLogs;
use SVG\Nodes\Structures\SVGGroup;

/**
 * Resize small QR code shapes  
 * it will scale up the content and viewport to the 
 * specified SACLE_UP_WIDTH width and height
 */
class IconShapeResizerProcessor extends BaseProcessor
{
    use WriteLogs;

    public const SACLE_UP_WIDTH = 500;

    public const ID_SCALED_UP_GROUP = 'scaled-up-by-icon-shape-resizer';

    protected function shouldProcess()
    {
        $width = $this->s->width($this->svg());

        return $width < static::SACLE_UP_WIDTH;
    }

    protected function process()
    {
        $this->scaleUpNodes();

        $this->resizeDocument();
    }

    private function resizeDocument()
    {
        $this->doc()->setAttribute('viewBox', $this->getScaledViewBox());

        $this->s->syncDimensionsWithViewBox($this->svg());
    }

    private function getScaledViewBox()
    {
        return sprintf('0 0 %1$s %2$s', static::SACLE_UP_WIDTH, $this->getScale() * $this->s->height($this->svg()));
    }

    private function scaleUpNodes()
    {
        $scaledGroup = new SVGGroup();

        $scaledGroup->setAttribute('id', static::ID_SCALED_UP_GROUP);

        $scaledGroup->setAttribute(
            'transform',
            sprintf('scale(%s)', $this->getScale())
        );

        foreach ($this->getNodesToScale() as $node) {
            $scaledGroup->addChild($node);
        }

        $this->doc()->addChild($scaledGroup);
    }

    private function getNodesToScale()
    {
        $maskedGroup = $this->doc()->getElementById(
            $this->s->getMaskedGroupId(OutlinedShape::ID_MASK)
        );

        $frameNode = $this->doc()->getElementById(
            OutlinedShape::ID_FRAME_NODE
        );

        $nodes = array_filter([
            $maskedGroup,
            $frameNode
        ]);

        if (empty($nodes)) {
            // we are not using outlined shape.

            return $nodes = $this->doc()->getElementsByClassName('qrcode');
        }

        return $nodes;
    }

    private function getScale()
    {
        $width = $this->s->width($this->svg());

        return static::SACLE_UP_WIDTH / $width;
    }

    /**
     * Should be executed after the following processors
     * 
     * @see \App\Support\CompatibleSVG\Processors\Shapes\OutlinedShape
     * @see \App\Support\CompatibleSVG\Processors\Shapes\OutlinedShapeModifier
     * 
     * And before the following processor
     * 
     * @see \App\Support\CompatibleSVG\Processors\LogoProcessor
     */
    public function sortOrder()
    {
        return 100;
    }
}
