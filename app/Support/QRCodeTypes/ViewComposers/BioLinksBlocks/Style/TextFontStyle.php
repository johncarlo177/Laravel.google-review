<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style;

use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\BlockModel;

class TextFontStyle
{
    private $selector = null;

    private ?BlockModel $model = null;

    private $prefix = '';

    public static function withSelector($selector)
    {
        $instance = new static;

        $instance->selector = $selector;

        return $instance;
    }

    public function withPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function withModel(BlockModel $model)
    {

        $this->model = $model;

        return $this;
    }

    private function key($name)
    {
        if (empty($this->prefix)) {
            return $name;
        }

        return sprintf('%s_%s', $this->prefix, $name);
    }

    public function generate()
    {
        return CssRuleGenerator::withSelector($this->selector)
            ->withModel($this->model)
            ->rule('font-weight', $this->key('font_weight'))
            ->rule('text-decoration', $this->key('text_decoration'))
            ->rule('font-style', $this->key('font_style'))
            ->rule('font-size', $this->key('font_size'), 'px')
            ->rule('text-align', $this->key('text_align'))
            ->generate();
    }
}
