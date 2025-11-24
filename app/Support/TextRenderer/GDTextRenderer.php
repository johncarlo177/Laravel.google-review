<?php

namespace App\Support\TextRenderer;

use App\Support\System\Traits\WriteLogs;
use Imagick;

class GDTextRenderer extends BaseTextRenderer
{
    use WriteLogs;

    /**
     * Check if GD and Imagick are available
     */
    public function isSupported()
    {
        return function_exists('gd_info') && class_exists(Imagick::class);
    }

    /**
     * Render the text safely using GD and return Imagick
     */
    protected function write(): ?Imagick
    {
        $this->logDebug('Before write');

        $text = $this->text;
        $this->logDebug('text assigned');

        $fontFile = $this->fontFile;

        $this->logDebug('Font file: %s', $fontFile);

        if (!file_exists($fontFile) || !is_readable($fontFile)) {
            $this->logWarning("Font file missing or unreadable: $fontFile");
            return null;
        }

        $fontSize = 140;

        // Calculate text bounding box
        $box = imagettfbbox($fontSize, 0, $fontFile, $text);
        if (!$box) {
            $this->logWarning("Failed to calculate text bounding box");
            return null;
        }

        $w = abs($box[2] - $box[0]);
        $h = abs($box[7] - $box[1]);
        $this->logDebug("Calculated width: $w, height: $h");

        // Create GD image
        $image = imagecreatetruecolor($w, $h);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);
        $this->logDebug('GD image created and filled');

        // Allocate text color
        [$r, $g, $b] = $this->hexToRgb($this->color);
        $textColor = imagecolorallocate($image, $r, $g, $b);
        $this->logDebug("Text color allocated: $r,$g,$b");

        // Draw the text
        $y_offset = abs($box[7]);
        imagettftext($image, $fontSize, 0, 0, $y_offset, $textColor, $fontFile, $text);
        $this->logDebug('Text drawn');

        // Capture GD image into memory
        ob_start();
        imagepng($image);
        $pngData = ob_get_clean();
        imagedestroy($image);
        $this->logDebug('GD image captured to memory');

        // Load into Imagick
        try {
            $imagick = new Imagick();
            $imagick->readImageBlob($pngData);
            $imagick->setImageFormat('png');
            $this->logDebug('Imagick image created from GD');
        } catch (\ImagickException $e) {
            $this->logWarning("Failed to create Imagick image: " . $e->getMessage());
            return null;
        }

        $this->logDebug('After write');
        return $imagick;
    }

    /**
     * Convert hex color (e.g., #ff0000) to RGB array
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 6) {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        } elseif (strlen($hex) === 3) {
            $r = hexdec(str_repeat(substr($hex, 0, 1), 2));
            $g = hexdec(str_repeat(substr($hex, 1, 1), 2));
            $b = hexdec(str_repeat(substr($hex, 2, 1), 2));
        } else {
            $r = $g = $b = 0;
        }

        return [$r, $g, $b];
    }
}
