<?php

namespace App\Support\TextRenderer;

use App\Support\GoogleFonts;
use Illuminate\Support\Facades\Log;
use Imagick;

abstract class BaseTextRenderer
{
    protected $text;

    protected $fontFile;

    protected $fontFamily;

    protected $fontVariant;

    protected $color;

    protected $fonts;

    public abstract function isSupported();

    public function render(string $text, string $fontFamily, string $fontVariant, string $color): ?Imagick
    {
        $this->text = $text;

        $this->fontFamily = $fontFamily;

        $this->fontVariant = $fontVariant;

        $this->color = $color;

        $this->fonts = new GoogleFonts();

        $this->fontFile = $this->fonts->getFontFile($this->fontFamily, $this->fontVariant);

        if (!$this->isSupported()) {
            return null;
        }

        return $this->write();
    }

    protected abstract function write(): ?Imagick;

    public static function detectSupportedRenderer(): ?BaseTextRenderer
    {
        $renderers = [
            InkscapeTextRenderer::class,
            ImagickTextRenderer::class,
            GDTextRenderer::class,
        ];

        foreach ($renderers as $rendererClass) {
            $renderer = new $rendererClass;

            if ($renderer->isSupported()) {
                return $renderer;
            }
        }

        return null;
    }
}
