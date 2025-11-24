<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style;

use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\BlockModel;
use App\Support\System\Traits\WriteLogs;

use function PHPUnit\Framework\isEmpty;

class CssRuleItem
{
    use WriteLogs;

    public $cssProperty, $modelKey;

    protected $unit = null;

    private ?BlockModel $model = null;

    public static function withModel(?BlockModel $model = null)
    {
        $instance = new static;

        $instance->model = $model;

        return $instance;
    }

    /**
     * @param string $cssProperty
     * @param string|callback $modelKey
     * @param string $unit
     */
    public function set($cssProperty, $modelKey, $unit = null)
    {
        $this->cssProperty = $cssProperty;

        $this->modelKey = $modelKey;

        $this->unit = $unit;

        return $this;
    }

    private function value()
    {
        if (is_callable($this->modelKey)) {
            $value = call_user_func($this->modelKey, $this);
        } else {
            $value = $this->model->field($this->modelKey);
        }

        if (empty($value)) return;

        return sprintf('%s%s', $value, $this->unit ?? '');
    }

    public function isNotEmpty()
    {
        return !$this->isEmpty();
    }

    public function isEmpty()
    {
        return empty($this->value());
    }

    public function generate()
    {
        // if ($this->isEmpty()) {
        //     $this->logDebug('%s is empty', $this->cssProperty);
        // }

        // $this->logDebug('%s: %s', $this->cssProperty, $this->value());

        return sprintf('%s: %s', $this->cssProperty, $this->value());
    }
}
