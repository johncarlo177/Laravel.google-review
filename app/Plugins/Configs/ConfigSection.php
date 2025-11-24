<?php

namespace App\Plugins\Configs;


class ConfigSection
{
    public $title;

    /**
     * @var ConfigDef[]
     */
    public array $fields;

    public $doNotRenderOuterSection = false;

    public function __construct($title, $fields, $doNotRenderOuterSection = false)
    {
        $this->title = $title;
        $this->fields = $fields;
        $this->doNotRenderOuterSection = $doNotRenderOuterSection;
    }

    public function toArray()
    {
        return (array)$this;
    }
}
