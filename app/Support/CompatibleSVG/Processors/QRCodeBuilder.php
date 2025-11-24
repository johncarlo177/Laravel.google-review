<?php

namespace App\Support\CompatibleSVG\Processors;

use App\Support\QRCodeProcessors\SvgBuilder;
use App\Support\QRCodeTypes\QRCodeTypeManager;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use SVG\SVG;

class QRCodeBuilder extends BaseProcessor
{
    protected QRCodeTypeManager $types;

    public function __construct()
    {
        $this->types = app(QRCodeTypeManager::class);
    }

    public function sortOrder()
    {
        return 0;
    }

    protected function data()
    {
        return $this->types->find($this->qrcode()->type)->makeData($this->qrcode());
    }

    protected function process()
    {
        $options = new QROptions([
            'version'             => QRCode::VERSION_AUTO,
            'outputType'          => QRCode::OUTPUT_MARKUP_SVG,
            'imageBase64'         => false,
            'eccLevel'            => SvgBuilder::getErrorCorrectionLevel(),
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

        $string = (new QRCode($options))->render($this->data());

        $this->payload->svg = SVG::fromString($string);

        $this->s->syncDimensionsWithViewBox($this->svg());
    }
}
