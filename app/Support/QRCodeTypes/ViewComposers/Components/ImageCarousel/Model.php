<?php

namespace App\Support\QRCodeTypes\ViewComposers\Components\ImageCarousel;

class Model
{
    public $mode = 'disabled';

    public $loop = 'disabled';

    public $auto_play = 'disabled';

    public $slides_to_show = 1;

    public $autoplay_interval = 2;

    public $navigation = 'disabled';

    public $navigation_color = '#fff';

    /**
     * @var array
     */
    public $items;

    public static function constructFrom($array)
    {
        $instance = new static;

        $attributes = get_object_vars($instance);

        foreach ($attributes as $key => $value) {
            $instance->{$key} = @$array[$key];
        }

        return $instance;
    }

    public function hasNavigation()
    {
        return $this->navigation === 'enabled';
    }

    public function isEnabled()
    {
        return $this->mode === 'enabled';
    }

    public function getItems()
    {
        return collect($this->items)
            ->filter(function ($item) {
                return file_url(@$item['image']);
            })
            ->values();
    }

    public function isEmpty()
    {
        return $this->getItems()->isEmpty();
    }

    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    public function toOptionsArray()
    {
        return [
            'loop' => $this->loop === 'enabled',
            'autoPlay' => $this->auto_play === 'enabled',
            'slidesToShow' => $this->slides_to_show ?? 1,
            'autoplayInterval' => !is_nan($this->autoplay_interval) && $this->autoplay_interval ? $this->autoplay_interval * 1000 : 3000
        ];
    }

    public function getOptions()
    {
        return base64_encode(json_encode($this->toOptionsArray()));
    }
}
