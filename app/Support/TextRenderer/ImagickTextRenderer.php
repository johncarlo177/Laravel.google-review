<?php

namespace App\Support\TextRenderer;

use App\Support\System\Traits\WriteLogs;
use Imagick;
use ImagickDraw;
use ImagickPixel;

class ImagickTextRenderer extends BaseTextRenderer
{
    use WriteLogs;

    public function isSupported()
    {
        return class_exists(Imagick::class);
    }

    protected function write(): ?Imagick
    {
        $this->logDebug('Before write');

        $text = $this->text;
        $this->logDebug('text assigned');

        $image = new Imagick();
        $this->logDebug('created Imagick');

        $draw = new ImagickDraw();
        $this->logDebug('created ImagickDraw');

        $fillPixel = new ImagickPixel('transparent');
        $this->logDebug('created fillPixel');

        $textPixel = new ImagickPixel($this->color);
        $this->logDebug('created textPixel');

        /* Black text */
        $draw->setFillColor($textPixel);
        $this->logDebug('setFillColor');

        $this->logDebug('Font %s', $this->fontFile);

        if (!file_exists($this->fontFile)) {
            $this->logWarning("Font file does not exist: " . $this->fontFile);
        }

        if (!is_readable($this->fontFile)) {
            $this->logWarning("Font NOT readable: " . $this->fontFile);
        }

        if (file_get_contents($this->fontFile) === false) {
            $this->logWarning("Cannot get contnet of: " . $this->fontFile);
        }

        $configure = $image->getConfigureOptions();

        $this->logDebug('Delegates %s', $configure['DELEGATES']);

        /* Font properties */
        $draw->setFont($this->fontFile);
        $this->logDebug('setFont');

        $draw->setFontSize(140);
        $this->logDebug('setFontSize');

        $metrix = $image->queryFontMetrics($draw, $text);
        $this->logDebug('queryFontMetrics');

        $w = $metrix['textWidth'];
        $this->logDebug('assigned width');

        $h = $metrix['textHeight'];
        $this->logDebug('assigned height');

        /* New image */
        $image->newImage($w, $h, $fillPixel);
        $this->logDebug('newImage');

        /* Create text */
        $image->annotateImage(
            $draw,
            0,
            $h - $h / 4,
            0,
            $text
        );
        $this->logDebug('annotateImage');

        /* Give image a format */
        $image->setImageFormat('png');
        $this->logDebug('setImageFormat');

        $image->trimImage(0);
        $this->logDebug('trimImage');

        $this->logDebug('After write');

        return $image;
    }
}
