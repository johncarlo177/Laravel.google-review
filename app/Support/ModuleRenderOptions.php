<?php

namespace App\Support;

use App\Models\QRCode;
use chillerlan\QRCode\Data\QRMatrix;
use chillerlan\Settings\SettingsContainerInterface;

class ModuleRenderOptions
{
    public QRMatrix $matrix;

    public SettingsContainerInterface $options;

    public int $moduleCount;

    public int $x, $y;

    public string $output;

    public QRCode $qrcode;

    public function __construct(
        int $x,
        int $y,
        QRMatrix $matrix,
        SettingsContainerInterface $options,
        $moduleCount,
        QRCode $qrcode,
        $output = ''
    ) {
        $this->x = $x;

        $this->y = $y;

        $this->matrix = $matrix;

        $this->options = $options;

        $this->moduleCount = $moduleCount;

        $this->qrcode = $qrcode;

        $this->output = $output;
    }
}
