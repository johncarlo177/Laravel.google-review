<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style;

use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\BlockModel;
use App\Support\System\Traits\WriteLogs;

class CssRuleGenerator
{
    use WriteLogs;

    private $selector = null;

    private ?BlockModel $model = null;

    private $rules = [];

    public static function withSelector($selector)
    {
        $instance = new static;

        $instance->selector = $selector;

        return $instance;
    }



    public function withModel(BlockModel $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @param string $cssProperty
     * @param string|callback $modelKey
     * @param string $unit
     */
    public function rule($cssProperty, $modelKey, $unit = null)
    {
        $this->rules[] = CssRuleItem::withModel($this->model)
            ->set($cssProperty, $modelKey, $unit);

        return $this;
    }

    private function getActualRules()
    {
        return collect($this->rules)
            ->filter(function (CssRuleItem $item) {
                return $item->isNotEmpty();
            })
            ->values();
    }

    private function joinedRules()
    {
        $joined = $this->getActualRules()
            ->map(function (CssRuleItem $rule) {
                return $rule->generate();
            })->join('; ');

        $joined = "$joined;";

        return $joined;
    }

    public function generate()
    {
        if ($this->getActualRules()->isEmpty()) return '';

        $generated = sprintf(
            '%s { %s }',
            $this->selector,
            $this->joinedRules()
        );

        return $generated;
    }

    public function __toString()
    {
        return $this->generate();
    }
}
