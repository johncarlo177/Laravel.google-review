<?php

namespace App\Support\ViewComposers\StackComponent;

class StackItem
{
    public $id = null,
        $title = null,
        $text = null,
        $background_color = null,
        $text_color = null,
        $image = null,
        $video = null;

    public static function fromArray($array): static
    {
        $instance = new static;

        foreach ($instance as $key => $value) {
            $instance->{$key} = @$array[$key];
        }

        return $instance;
    }

    public function getCssId()
    {
        return 'stack-item-'  . $this->id;
    }

    public function imageUrl()
    {
        return file_url($this->image);
    }

    public function videoUrl()
    {
        return file_url($this->video);
    }
}
