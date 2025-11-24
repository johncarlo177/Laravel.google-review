<?php

namespace App\Support\QRCodeProcessors;

use App\Interfaces\FileManager;

use Imagick;
use App\Interfaces\QRCodeProcessor;
use App\Models\File;
use App\Models\QRCode;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use SVG\Nodes\Embedded\SVGImage;
use SVG\Nodes\Shapes\SVGCircle;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGGroup;
use SVG\SVG;

class LogoProcessor extends BaseProcessor implements QRCodeProcessor
{
    protected FileManager $files;

    protected QRCode $qrcode;

    protected File $logo;

    public function __construct(FileManager $files)
    {
        $this->files = $files;
    }

    protected function shouldProcess(): bool
    {
        return false;
    }

    public function hasLogo()
    {
        $qrcode = $this->qrcode;

        $logoType = $qrcode->design->logoType;

        $logoUrl = $qrcode->design->logoUrl;

        $logoUrl = str_replace(url('/'), '', $logoUrl);

        $logoUrl = rtrim($logoUrl, '/');

        $logo = $qrcode->getLogo();

        $hasCustomLogo = $logoType === 'custom' && !empty($logo);

        $hasPresetLogo = $logoType === 'preset' && !empty($logoUrl);

        $hasLogo = $hasPresetLogo || $hasCustomLogo;

        return $hasLogo;
    }

    protected function shouldPostProcess()
    {
        return static::hasLogo($this->qrcode);
    }

    protected function process() {}

    protected function postProcess()
    {
        $this->writeLogo();
    }

    public function writeLogo()
    {
        $logo = $this->makeLogo();

        if ($logo)
            $this->doc->addChild($logo);
    }

    public static function getInstance($qrcode, SVG $svg)
    {
        /**
         * @var static
         */
        $instance = app(static::class);

        $instance->svg = $svg;

        $instance->doc = $svg->getDocument();

        $instance->qrcode = $qrcode;

        return $instance;
    }

    public function makeLogo()
    {
        $logoImage = $this->makeImagickLogoImage();

        if (!$logoImage) return;

        $logoImage->resizeImage(
            $this->getSvgViewBoxSize(),
            $this->getSvgViewBoxSize(),
            Imagick::FILTER_UNDEFINED,
            1,
            bestfit: true,
        );

        $width = $logoImage->getImageWidth();

        $height = $logoImage->getImageHeight();

        $embdedImage = 'data:' . $logoImage->getImageMimeType() .
            ';base64,' . base64_encode($logoImage->__toString());

        $posX = $this->qrcode->design->logoPositionX;

        $posY = 1 - $this->qrcode->design->logoPositionY;

        $start = $this->getViewBoxStart();

        $maxScale = 0.75;

        $minScale = 0.05;

        $scale = min($this->qrcode->design->logoScale, $maxScale);

        $scale = max($minScale, $scale);

        $rotate = $this->qrcode->design->logoRotate;

        $viewBoxSize = $this->getSvgViewBoxSize();

        $x = $viewBoxSize * $posX;

        $y = $viewBoxSize * $posY;

        $logoBackgroundShape = $this->qrcode->design->logoBackgroundShape;

        list($cx) = $this->getViewboxCenter();

        if ($logoBackgroundShape === 'circle') {
            $bg = new SVGCircle($cx, $cx, $viewBoxSize / 2);
        } else if ($logoBackgroundShape === 'square') {
            $bg = new SVGRect($start, $start, $viewBoxSize, $viewBoxSize);
        } else {
            throw new InvalidArgumentException("Logo background shape is invalid ($logoBackgroundShape)");
        }

        $logoBackgroundEnabled = $this->qrcode->design->logoBackground;

        if ($logoBackgroundEnabled) {
            $bg->setAttribute('fill', $this->qrcode->design->logoBackgroundFill);
        } else {
            $bg->setAttribute('fill', 'none');
        }

        $bgScale = $this->qrcode->design->logoBackgroundScale;

        $bg->setAttribute('transform', "scale($bgScale)");

        $bg->setAttribute('transform-origin', "$cx $cx");

        $img = new SVGImage(
            $embdedImage,
            $start + ($viewBoxSize - $width) / 2,
            $start + ($viewBoxSize - $height) / 2,
            $width,
            $height
        );

        $logo = new SVGGroup();

        $logo->addChild($bg);

        $logo->addChild($img);

        $logo->setAttribute(
            'transform',
            sprintf(
                'rotate(%s) scale(%s)',
                $rotate,
                $scale
            )
        );

        $logo->setAttribute(
            'transform-origin',
            sprintf(
                '%1$s %2$s',
                $start + $viewBoxSize / 2,
                $start + $viewBoxSize / 2
            )
        );

        $translateLayer = new SVGGroup();

        $translateLayer->addChild($logo);

        $translateLayer->setAttribute(
            'transform',
            sprintf(
                'translate(%s %s)',
                $x - $viewBoxSize / 2,
                $y - $viewBoxSize / 2
            )
        );

        $logoImage->destroy();

        return $translateLayer;
    }

    public function makeImagickLogoImage(): ?Imagick
    {
        if ($this->qrcode->design->logoType === 'custom') {
            $raw = $this->files->raw($this->qrcode->getLogo());

            $logoImage = new Imagick();

            $logoImage->readImageBlob($raw);
        } else if ($this->qrcode->design->logoType === 'preset') {

            try {
                $content = file_get_contents($this->qrcode->design->logoUrl);

                $logoImage = new Imagick();

                $logoImage->readImageBlob($content);
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
                return null;
            }
        } else {
            throw new \InvalidArgumentException('Invalid logo type');
        }

        return $logoImage;
    }

    protected function getViewboxCenter()
    {
        $doc = $this->svg->getDocument();

        $viewBox = explode(' ', $doc->getAttribute('viewBox'));

        $x = $viewBox[0];

        $w = $viewBox[2];

        $y = $viewBox[1];

        $h = $viewBox[3];

        $center = [
            ($w / 2 + $x),
            ($h / 2 + $y)
        ];

        return $center;
    }
}
