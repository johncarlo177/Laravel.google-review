<?php

namespace App\Support\QRCodeProcessors;

use App\Interfaces\FileManager;
use App\Interfaces\QRCodeProcessor;
use Imagick;
use SVG\Nodes\Embedded\SVGImage;
use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Structures\SVGClipPath;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Structures\SVGStyle;

class ForegroundImageProcessor extends BaseProcessor implements QRCodeProcessor
{
    protected FileManager $files;

    use Traits\JoinsForegroundMasks;

    protected static $joinedMaskId = 'joined-mask-for-foreground-image';

    public function __construct(FileManager $files)
    {
        $this->files = $files;
    }

    protected function shouldProcess(): bool
    {
        $fillType = $this->qrcode->design->fillType;

        $foregroundImage = $this->qrcode->getForegroundImage();

        return $fillType === 'foreground_image' && !empty($foregroundImage);
    }

    protected function process()
    {
        $container = $this->getForegroundContainer();

        $foreground = new SVGImage();

        $foreground->setAttribute('class', 'dark type-data');

        $foreground->setAttribute(
            'mask',
            sprintf('url(#%s)', $this::$joinedMaskId)
        );

        $foreground->setAttribute('x', $this->getViewBoxStart());

        $foreground->setAttribute('y', $this->getViewBoxStart());

        $foreground->setAttribute('xlink:href', $this->getForegroundImage());

        $this->addChild($container, $foreground);
    }

    protected function getForegroundImage()
    {
        $path = $this->files->path($this->qrcode->getForegroundImage());

        $image = new Imagick($path);

        $image = $image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

        $size = $this->getSvgViewBoxSize();

        $iW = $image->getImageWidth();

        $iH = $image->getImageHeight();

        $aspect = $iW / $iH;

        if ($iW != $size || $iH != $size) {

            if ($iW < $iH) {
                $image->scaleImage($size, $size / $aspect);
            } else {
                $image->scaleImage($size * $aspect, $size);
            }

            $image->cropImage($size, $size, 0, 0);
        }

        $embdedImage = 'data:' . $image->getImageMimeType() . ';base64,' . base64_encode($image->__toString());

        $image->destroy();

        return $embdedImage;
    }
}
