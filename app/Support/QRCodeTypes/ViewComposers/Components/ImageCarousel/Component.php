<?php

namespace App\Support\QRCodeTypes\ViewComposers\Components\ImageCarousel;

class Component
{
    protected Model $model;

    public static function withData($data)
    {
        $instance = new static;

        $instance->model = Model::constructFrom($data);

        return $instance;
    }

    public function render()
    {
        if (!$this->model->isEnabled()) {
            return;
        }

        if ($this->model->isEmpty()) {
            return;
        }

        return view('qrcode.components.image-carousel', [
            'model' => $this->model
        ]);
    }
}
