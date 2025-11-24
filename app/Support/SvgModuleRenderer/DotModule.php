<?php

namespace App\Support\SvgModuleRenderer;

use App\Interfaces\ModuleRenderer;

class DotModule extends BaseRenderer implements ModuleRenderer
{
    protected function shouldRenderSingleModule()
    {
        return $this->qrcode->design->module === 'dots'
            && $this->matrix->checkTypeNotIn(
                $this->x,
                $this->y,
                $this->options->keepAsSquare
            );
    }

    protected function singleModuleCommands()
    {
        $r = 0.5;

        return $this->pathCommands(
            $this->scale * ($this->x + 0.5 - $r),
            $this->scale * ($this->y + 0.5),
            $this->scale * $r,
            $this->scale * ($r * 2)
        );
    }

    protected function pathCommands()
    {
        $cx = func_get_arg(0);
        $cy = func_get_arg(1);

        $rx = func_get_arg(2);
        $ry = func_get_arg(3);

        return sprintf(
            ' M%1$s %2$s a%3$s %3$s 0 1 0 %4$s 0 a%3$s %3$s 0 1 0 -%4$s 0Z',
            $cx,
            $cy,
            $rx,
            $ry
        );
    }
}
