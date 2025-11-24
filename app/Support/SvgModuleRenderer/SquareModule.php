<?php

namespace App\Support\SvgModuleRenderer;

use App\Interfaces\ModuleRenderer;

class SquareModule extends BaseRenderer implements ModuleRenderer
{
    protected function pathCommands()
    {
        $x = func_get_arg(0);
        $y = func_get_arg(1);
        $width = func_get_arg(2);
        $height = func_get_arg(3);

        return sprintf(
            ' M%1$s %2$s h%3$s v%4$s h-%3$sZ',
            $x,
            $y,
            $width,
            $height
        );
    }

    protected function shouldRenderSingleModule()
    {

        if ($this->checkTypeIn($this->x, $this->y, [
            $this->matrix::M_FINDER,
            $this->matrix::M_FINDER_DOT
        ])) {
            return true;
        }

        return (empty($this->qrcode->design->module)
            || $this->qrcode->design->module === 'square'
        ) || $this->checkTypeIn(
            $this->x,
            $this->y,
            $this->options->keepAsSquare
        );
    }

    protected function singleModuleCommands()
    {
        return $this->pathCommands(
            $this->x * $this->scale,
            $this->y * $this->scale,
            $this->scale,
            $this->scale
        );
    }
}
