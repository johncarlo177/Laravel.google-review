<?php

namespace App\Support\CompatibleSVG;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;
use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGDefs;
use SVG\Nodes\Structures\SVGDocumentFragment;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Structures\SVGMask;
use SVG\Nodes\SVGNode;
use SVG\Nodes\SVGNodeContainer;
use SVG\SVG;

class Expirement
{
    public function run($data = 'this is test QR code.')
    {
        $options = new QROptions([
            'version'             => QRCode::VERSION_AUTO,
            'outputType'          => QRCode::OUTPUT_MARKUP_SVG,
            'imageBase64'         => false,
            'eccLevel'            => EccLevel::L,
            'addQuietzone'        => false,
            // if set to true, the light modules won't be rendered
            'imageTransparent'    => false,
            // empty the default value to remove the fill* attributes from the <path> elements
            'markupDark'          => '',
            'markupLight'         => '',
            // draw the modules as circles isntead of squares
            'drawCircularModules' => false,
            'circleRadius'        => 0.4,
            // connect paths
            'connectPaths'        => true,
            // keep modules of thhese types as square
            'keepAsSquare'        => [
                QRMatrix::M_FINDER | QRMatrix::IS_DARK,
                QRMatrix::M_FINDER_DOT,
                QRMatrix::M_ALIGNMENT | QRMatrix::IS_DARK,
            ],
            // https://developer.mozilla.org/en-US/docs/Web/SVG/Element/linearGradient
            'svgDefs'             => '
	<style><![CDATA[
		.light{fill: white;}
	]]></style>',
        ]);

        $string = (new QRCode($options))->render($data);

        $qrcode = SVG::fromString($string);

        $qrcode->getDocument()
            ->setWidth($qrcode->getDocument()->getViewBox()[2]);

        $qrcode->getDocument()
            ->setHeight($qrcode->getDocument()->getViewBox()[3]);

        $shape = SVG::fromFile(__DIR__ . '/svg/cloud-1.svg');

        $shapeDoc = $shape->getDocument();

        $shapePath = $this->getMaskPath($shapeDoc);

        $group = $this->includeQRCode(
            $shape,
            $qrcode
        );

        $this->generateDummyData($qrcode, $shape, $group);

        $this->makeMask($shapePath, $shapeDoc, $group);

        Storage::put('tmp-qrcode.svg', $shape->toXMLString());
    }

    /**
     * @return SVGNodeContainer
     */
    private function getDefs(SVGNodeContainer $container)
    {
        $defs = @$container->getElementsByTagName('defs')[0];

        if (!$defs) {
            $defs = new SVGDefs();

            $container->addChild($defs, 0);
        }

        return $defs;
    }

    private function makeMask(SVGPath $path, SVGNodeContainer $parent, SVGNode $target)
    {
        $mask = new SVGMask();

        $mask->setAttribute('maskUnits', "userSpaceOnUse");

        $path->setStyle('fill', '#ffffff');

        $mask->addChild($path);

        $mask->setAttribute('id', 'shape-mask');

        $defs = $this->getDefs($parent);

        $defs->addChild($mask);

        // Wrap target in a new group, because mask won't work with a target that has transform attribute

        $g = new SVGGroup();

        $g->addChild($target);

        $g->setAttribute('mask', sprintf('url(#%s)', $mask->getAttribute('id')));

        $parent->addChild($g);

        return $mask;
    }

    private function getMaskPath(SVGDocumentFragment $shape)
    {
        return $shape->getElementById('mask-path');
    }

    private function width(SVG $svg)
    {
        return intval($svg->getDocument()->getWidth());
    }

    private function height(SVG $svg)
    {
        return intval($svg->getDocument()->getHeight());
    }

    private function includeQRCode(SVG $shapeSvg, SVG $qrcodeSvg)
    {
        $qrcode = $qrcodeSvg->getDocument();
        $shape = $shapeSvg->getDocument();

        $qrcodeDarkPath = $qrcode->getElementsByClassName('dark')[0];

        $qrcodeDarkPath->setAttribute('transform', sprintf(
            'translate(%s, %s)',
            $this->getQRCodePosition()[0],
            $this->getQRCodePosition()[1]
        ));

        $background = $this->makeQRCodeBackground($qrcodeSvg);

        $group = new SVGGroup();

        $group->addChild($background);

        $group->addChild($qrcodeDarkPath);

        $group->setAttribute('transform', sprintf('scale(%s)', $this->getScale()));

        $shape->addChild($group);

        return $group;
    }

    private function getScale()
    {
        return 0.35;
    }

    private function getQRCodePosition()
    {
        return [26, 27];
    }

    private function makeQRCodeBackground(SVG $qrcode, $margin = 1)
    {
        list($x, $y) = $this->getQRCodePosition();

        $rect = new SVGRect(
            $x - $margin,
            $y - $margin,
            $this->width($qrcode) + $margin * 2,
            $this->height($qrcode) + $margin * 2
        );

        $rect->setAttribute('fill', '#ffffff');

        return $rect;
    }

    private function generateDummyData(SVG $qrcode, SVG $shape, $parent)
    {
        $width = $this->width($shape) / $this->getScale();
        $height = $this->height($shape) / $this->getScale();

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

        $parent->addChild($path, 0);
    }
}
