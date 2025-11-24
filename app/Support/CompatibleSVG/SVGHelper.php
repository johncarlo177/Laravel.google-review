<?php

namespace App\Support\CompatibleSVG;

use Imagick;
use SVG\Nodes\Embedded\SVGImage;
use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGDefs;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Structures\SVGMask;
use SVG\Nodes\SVGNode;
use SVG\SVG;

class SVGHelper
{
    public function doc(SVG $svg)
    {
        return $svg->getDocument();
    }

    public function getById(SVG $svg, $id)
    {
        return $this->doc($svg)->getElementById($id);
    }

    public function syncDimensionsWithViewBox(SVG $svg)
    {
        $this->doc($svg)
            ->setWidth($this->doc($svg)->getViewBox()[2] . 'pt');

        $this->doc($svg)
            ->setHeight($this->doc($svg)->getViewBox()[3] . 'pt');
    }

    public function width(SVG $svg)
    {
        return floatval($svg->getDocument()->getWidth());
    }

    public function height(SVG $svg)
    {
        return floatval($svg->getDocument()->getHeight());
    }

    /**
     * @return SVGNodeContainer
     */
    public function defs(SVG $svg)
    {
        $defs = @$this->doc($svg)->getElementsByTagName('defs')[0];

        if (!$defs) {
            $defs = new SVGDefs();

            $this->doc($svg)->addChild($defs, 0);
        }

        return $defs;
    }

    /**
     * Mask target with path
     * @param string $maskId ex. shape-mask
     */
    public function mask(SVGNode $target, SVGPath $path, SVG $svg, $maskId)
    {
        $mask = new SVGMask();

        $mask->setAttribute('maskUnits', "userSpaceOnUse");

        $path->setStyle('fill', '#ffffff');

        $mask->addChild($path);

        $mask->setAttribute('id', $maskId);

        $defs = $this->defs($svg);

        $defs->addChild($mask);

        // Wrap target in a new group, because mask won't work with a target that has transform attribute

        $g = new SVGGroup();

        $g->addChild($target);

        $g->setAttribute(
            'mask',
            sprintf('url(#%s)', $mask->getAttribute('id'))
        );

        $g->setAttribute('id', $this->getMaskedGroupId($maskId));

        $this->doc($svg)->addChild($g);

        return $mask;
    }

    public function getMaskedGroupId($maskId)
    {
        return "group-masked-by-" . $maskId;
    }

    public function embedImagickImage(Imagick $image)
    {
        $base64 = 'data:' . $image->getImageMimeType() .
            ';base64,' . base64_encode($image->__toString());

        return new SVGImage(
            $base64,
            0,
            0,
            $image->getImageWidth(),
            $image->getImageHeight()
        );
    }

    public function crossAt(SVG $svg, $length = 2, $x = null, $y = null)
    {
        $w = $this->width($svg);
        $h = $this->height($svg);

        $x = $x === null ? $w / 2 : $x;
        $y = $y === null ? $h / 2 : $y;

        $vertical = new SVGRect($x - $length / 2, 0, $length, $h);

        $horizontal = new SVGRect(0, $y - $length / 2, $w, $length);

        $this->doc($svg)->addChild($vertical);
        $this->doc($svg)->addChild($horizontal);
    }

    public function clonePath(SVGPath $path)
    {
        $clone = new SVGPath($path->getAttribute('d'));

        $clone->setAttribute('transform', $path->getAttribute('transform'));

        foreach ($path->getSerializableStyles() as $name => $value) {
            // only copy stroke styles to clone
            if (preg_match('/stroke/', $name))
                $clone->setStyle($name, $value);
        }

        return $clone;
    }
}
