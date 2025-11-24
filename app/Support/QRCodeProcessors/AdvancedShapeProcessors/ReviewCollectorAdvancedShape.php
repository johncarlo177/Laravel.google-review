<?php

namespace App\Support\QRCodeProcessors\AdvancedShapeProcessors;

use App\Models\File;
use Imagick;
use SVG\Nodes\Embedded\SVGImage;
use SVG\SVG;
use Throwable;

class ReviewCollectorAdvancedShape extends BaseAdvancedShapeProcessor
{
    protected $id = 'review-collector';

    protected function postProcess()
    {
        parent::postProcess();

        $this->injectLogo();
    }

    protected function injectLogo()
    {
        $logoString = $this->makeLogoString();

        $image = new SVGImage($logoString);

        $logoPlaceholder = $this->doc->getElementById('logo_placeholder');

        $image->setAttribute('width', $logoPlaceholder->getAttribute('width'));
        $image->setAttribute('height', $logoPlaceholder->getAttribute('height'));
        $image->setAttribute('x', $logoPlaceholder->getAttribute('x'));
        $image->setAttribute('y', $logoPlaceholder->getAttribute('y'));

        $this->doc->addChild($image);
        $this->doc->removeChild($logoPlaceholder);
    }

    protected function makeLogoString()
    {
        $logo = $this->makeLogoStringFromFile();

        if ($logo) {
            return $logo;
        }

        $logo = $this->makeLogoStringFromStaticAssetUrl();

        return $logo;
    }

    protected function makeLogoStringFromStaticAssetUrl()
    {
        $assetFileName = $this->designValue('reviewCollectorLogoSrc');

        $url = override_asset('assets/images/review-collector-logos/' . $assetFileName . '.png');

        try {
            $content = file_get_contents($url);

            $logoImage = new Imagick();

            $logoImage->readImageBlob($content);

            return $this->inlineImagickHref($logoImage);
        } catch (Throwable $th) {
            return null;
        }
    }

    protected function makeLogoStringFromFile()
    {
        $logoId = $this->designValue('reviewCollectorLogo');

        $file = File::find($logoId);

        if (!$file) {
            return null;
        }

        $url = $this->files->url($file);

        $image = new Imagick($url);

        $logoString = $this->inlineImagickHref($image);

        return $logoString;
    }

    protected function generateStyles()
    {
        $styles = [
            $this->circleColorStyle(),
            $this->starsColorStyle(),
        ];

        $styles = array_filter($styles);

        if (!empty($styles)) {
            return implode("\n", $styles);
        }

        return '';
    }

    protected function circleColorStyle()
    {
        $circleColor = $this->designValue('reviewCollectorCircleColor');

        if (!$circleColor) {
            return null;
        }

        $styles = '#circle { stroke: %s!important; }';

        return sprintf($styles, $circleColor);
    }

    protected function starsColorStyle()
    {
        $starsColor = $this->designValue('reviewCollectorStarsColor');

        if (!$starsColor) {
            return null;
        }

        $styles = '#stars-group path { fill: %s; }';

        return sprintf($styles, $starsColor);
    }
}
