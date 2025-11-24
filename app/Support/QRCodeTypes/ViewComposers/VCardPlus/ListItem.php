<?php

namespace App\Support\QRCodeTypes\ViewComposers\VCardPlus;

class ListItem
{
    public $type = '';

    public $value = '';

    private $defaultType = '';

    public static function make($rawValue)
    {
        $value = (array) $rawValue;

        $instance = new static;

        $instance->type = @$value['type'];

        $instance->value = @$value['value'];

        return $instance;
    }


    public function withDefaultType($type)
    {
        $this->defaultType = $type;

        return $this;
    }

    public function isEmpty()
    {
        return empty($this->value);
    }

    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    public function getType()
    {
        $type = empty($this->type) ? $this->defaultType : $this->type;

        return VCardFileGenerator::escape($type);
    }

    public function getAndroidType()
    {
        return 'WORK';
    }

    public function getValue()
    {
        return VCardFileGenerator::escape($this->value);
    }

    public function toArray()
    {
        return [
            'type' => $this->getType(),
            'value' => $this->getValue(),
        ];
    }
}
