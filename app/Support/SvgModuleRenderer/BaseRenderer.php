<?php

namespace App\Support\SvgModuleRenderer;


use App\Models\QRCode;
use App\Support\ModuleRenderOptions;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\Settings\SettingsContainerInterface;
use Closure;

abstract class BaseRenderer
{
    protected ModuleRenderOptions $moduleRenderOptions;

    protected QRMatrix $matrix;

    protected SettingsContainerInterface $options;

    protected int $moduleCount;

    protected int $x, $y;

    protected string $output;

    protected QRCode $qrcode;

    protected $scale;

    private static $maxFinderX;

    private static $maxFinderY;

    public static $qrModuleCount;

    public static $alignmentModules = [];

    public static $qrcodeScale;

    protected const neighbours = [
        0b00000001 => [-1, -1],
        0b00000010 => [0, -1],
        0b00000100 => [1, -1],
        0b00001000 => [1,  0],
        0b00010000 => [1,  1],
        0b00100000 => [0,  1],
        0b01000000 => [-1,  1],
        0b10000000 => [-1,  0]
    ];

    public function handle(ModuleRenderOptions $moduleRenderOptions, Closure $next)
    {
        $this->moduleRenderOptions = $moduleRenderOptions;

        // Make render options available as class properties.
        foreach (get_object_vars($moduleRenderOptions) as $key => $value) {
            $this->$key = $value;
        }

        $this->scale = ($this->options->svgViewBoxSize /  $this->moduleCount);

        $this::$qrcodeScale = $this->scale;

        if (!$this->shouldRenderLightModules() && $this->isLight()) {
            return $next($moduleRenderOptions);
        }

        if (!$this->shouldRenderSingleModule()) {
            return $next($moduleRenderOptions);
        }

        $this::$qrModuleCount = $this->moduleCount;

        $this->detectMaxFinderPoint();

        $this->detectAlignmentPoints();

        $this->setUp();

        $this->renderSingleModule();

        $this->moduleRenderOptions->output = $this->output;

        return $next($this->moduleRenderOptions);
    }

    protected abstract function shouldRenderSingleModule();

    protected function shouldRenderLightModules()
    {
        return false;
    }

    protected function isLight()
    {
        return !$this->matrix->checkType($this->x, $this->y, QRMatrix::IS_DARK);
    }

    protected function setUp()
    {
    }

    protected abstract function singleModuleCommands();

    protected function renderSingleModule()
    {
        $this->output .= $this->singleModuleCommands();
    }

    protected abstract function pathCommands();

    private function detectMaxFinderPoint()
    {
        if (!$this->matrix->checkType($this->x, $this->y, $this->matrix::M_FINDER)) {
            return;
        }

        if ($this->x > $this->moduleCount / 3 || $this->y > $this->moduleCount / 3) {
            return;
        }

        $this::$maxFinderX = max($this->x, $this::$maxFinderX);

        $this::$maxFinderY = max($this->y, $this::$maxFinderY);
    }

    private function detectAlignmentPoints()
    {
        if (!$this->matrix->checkType($this->x, $this->y, $this->matrix::M_ALIGNMENT)) {
            return;
        }

        list($lastX, $lastY) = empty($this::$alignmentModules) ? [0, 0] : $this::$alignmentModules[count($this::$alignmentModules) - 1];

        if ($this->x - $lastX > 4 || $this->y - $lastY > 4)
            $this::$alignmentModules[] = [$this->x, $this->y];
    }

    public static function getMaxFinderPoint()
    {
        return [
            static::$maxFinderX,
            static::$maxFinderY
        ];
    }

    /**
     * Checks the status neighbouring modules of the given module at ($x, $y) and returns a bitmask with the results.
     *
     * The 8 flags of the bitmask represent the status of each of the neighbouring fields,
     * starting with the lowest bit for top left, going clockwise:
     *
     *   1 2 3
     *   8 # 4
     *   7 6 5
     *
     * @todo: when $M_TYPE_VALUE is given, it should check for the same $M_TYPE while igrnoring the IS_DARK flag
     *
     * (this method may be added to QRMatrix as direct array access is faster than method calls)
     */
    protected function checkNeighbours(int $x, int $y): int
    {
        $bits = 0;

        foreach ($this::neighbours as $bit => $coord) {
            if ($this->matrix->check($x + $coord[0], $y + $coord[1])) {
                $bits |= $bit;
            }
        }

        return $bits;
    }

    protected function checkTypeIn(int $x, int $y, array $M_TYPES): bool
    {
        return !$this->matrix->checkTypeNotIn($x, $y, $M_TYPES);
    }
}
