<?php

namespace App\Plugins\Configs;


class ConfigDef
{
    public $key, $expandedKey, $title, $type, $instructions, $placeholder, $extra, $defaultValue;

    public $value = null;

    public function __construct(
        $key,
        $title,
        $type,
        $placeholder = null,
        $instructions = null,
        $extra = null,
        $defaultValue = null,
    ) {
        $this->key = $key;
        $this->title = $title;
        $this->type = $type;
        $this->instructions = $instructions;
        $this->extra = $extra;
        $this->defaultValue = $defaultValue;

        $this->setPlaceholder($placeholder);
    }

    private function setPlaceholder($value)
    {
        if (!$value) {
            $this->placeholder = $this->defaultValue;
        } else {

            $this->placeholder = $value;
        }
    }

    public function toArray()
    {
        return (array) $this;
    }
}
