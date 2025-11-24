<?php


namespace App\Support\CompatibleSVG\Processors\Shapes\Traits;

use SVG\Nodes\Structures\SVGDocumentFragment;

trait FillsFrameColor
{
    /**
     * @return SVGDocumentFragment
     */
    protected abstract function doc();


    protected function process()
    {
        $this->fillFrameColor();
    }

    protected function fillFrameColor()
    {
        // Sync frame fill with frame color

        $nodes = array_merge(
            $this->doc()->getElementsByClassName('frame-node'),
            [
                $this->doc()->getElementById('frame-node')
            ]
        );

        $nodes = array_filter($nodes);

        foreach ($nodes as $node) {
            $node->setStyle('fill', $this->qrcode()->design->frameColor);
        }
    }
}
