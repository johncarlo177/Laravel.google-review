<?php

namespace App\Support;

use App\Models\QRCode;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\Settings\SettingsContainerInterface;
use Illuminate\Pipeline\Pipeline;

class SvgOutput extends QRMarkupSVG
{
    protected QRCode $qrcode;

    protected static $moduleRenderers = [];

    public function __construct(SettingsContainerInterface $options, QRMatrix $matrix, QRCode $qrcode)
    {
        parent::__construct($options, $matrix);

        $this->scale = ($this->options->svgViewBoxSize /  $this->moduleCount);

        $this->qrcode = $qrcode;
    }

    protected function module(int $x, int $y, int $M_TYPE): string
    {
        if ($this->options->imageTransparent && !$this->matrix->check($x, $y)) {
            return '';
        }

        $output = $this->pipe(
            new ModuleRenderOptions(
                $x,
                $y,
                $this->matrix,
                $this->options,
                $this->moduleCount,
                $this->qrcode
            )
        );

        return $output->output;
    }

    public static function moduleRenderer($renderer)
    {
        static::$moduleRenderers[] = $renderer;
    }

    private function pipe(ModuleRenderOptions $moduleRenderOptions): ModuleRenderOptions
    {
        $pipeline = new Pipeline(app());

        $pipeline->send($moduleRenderOptions)->through(static::$moduleRenderers);

        $output = $pipeline->thenReturn();

        return $output;
    }

    protected function paths(): string
    {
        $paths = $this->collectModules(fn (int $x, int $y, int $M_TYPE, int $M_TYPE_LAYER): string => $this->module($x, $y, $M_TYPE));
        $svg   = [];

        // create the path elements
        foreach ($paths as $M_TYPE => $path) {
            $path = trim(implode(' ', $path));

            if (empty($path)) {
                continue;
            }

            $cssClass = $this->mapCssClasses($M_TYPE);

            $format = empty($this->moduleValues[$M_TYPE])
                ? '<path class="%1$s" d="%2$s"/>'
                : '<path class="%1$s" fill="%3$s" fill-opacity="%4$s" d="%2$s"/>';


            $svgPath = sprintf($format, $cssClass, $path, $this->moduleValues[$M_TYPE], $this->options->svgOpacity);

            $svg[] = $svgPath;
        }

        return implode($this->options->eol, $svg);
    }

    protected function mapCssClasses($M_TYPE)
    {
        $map = [
            QRMatrix::M_ALIGNMENT           => 'alignment',
            QRMatrix::M_DATA                => 'data',
            QRMatrix::M_FINDER              => 'finder',
            QRMatrix::M_FINDER_DOT          => 'finder-dot',
            QRMatrix::M_FORMAT              => 'format',
            QRMatrix::M_LOGO                => 'logo',
            QRMatrix::M_QUIETZONE           => 'quite-zone',
            QRMatrix::M_SEPARATOR           => 'separator',
            QRMatrix::M_TIMING              => 'timing',
            QRMatrix::M_VERSION             => 'version',
        ];

        $mappedClass = '';

        foreach ($map as $key => $class) {
            if (($key & $M_TYPE) === $key) {
                $mappedClass = "type-$class ";
            }
        }

        $mappedClass = trim($mappedClass);

        return implode(' ', [
            'qr-' . $M_TYPE,
            ($M_TYPE & QRMatrix::IS_DARK) === QRMatrix::IS_DARK ? 'dark' : 'light',
            $mappedClass
        ]);
    }
}
