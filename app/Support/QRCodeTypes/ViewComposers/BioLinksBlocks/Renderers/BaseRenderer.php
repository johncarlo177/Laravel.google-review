<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Renderers;

use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\BlockModel;

abstract class BaseRenderer
{
    protected BlockModel $model;

    public static function make()
    {
        return new static;
    }

    public static function withModel(BlockModel $model)
    {
        $instance = new static;

        $instance->model = $model;

        return $instance;
    }

    public abstract function render();
}
