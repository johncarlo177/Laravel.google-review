<?php

namespace App\Support\QRCodeTypes\ViewComposers\VCardPlus;

use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Collection;

class ListBuilder
{
    use WriteLogs;

    protected $value = null;

    protected $fallbackList = [];

    protected $defaultType = '';

    protected $stringTransformer = null;

    /**
     * @var Collection
     */
    protected $builtList = null;

    public static function withValue($value)
    {
        $instance = new static;

        $instance->value = $value;

        return $instance;
    }

    public function withFallbackList($fallbackList)
    {
        $this->fallbackList = $fallbackList;

        return $this;
    }

    public function withDefaultType($defaultType)
    {
        $this->defaultType = $defaultType;

        return $this;
    }

    public function withStringTransformer($transformer)
    {
        $this->stringTransformer = $transformer;

        return $this;
    }

    protected function transform(ListItem $item)
    {
        if (!is_callable($this->stringTransformer)) {
            $this->logError('Transformer is not callable');

            return '';
        }

        return call_user_func($this->stringTransformer, $item);
    }

    public function build()
    {
        $list = collect($this->value);

        $list = $list
            ->map(function ($item) {
                return ListItem::make($item);
            })
            ->filter(function (ListItem $item) {
                return $item->isNotEmpty();
            })
            ->values();

        if ($list->isEmpty()) {
            $list = collect($this->fallbackList)
                ->map(function ($item) {
                    return ListItem::make($item);
                });
        }

        $this->builtList = $list;

        return $this;
    }

    public function getBuiltList()
    {
        return $this->builtList;
    }

    public function toString()
    {
        return $this->builtList->map(
            function ($item, $i) {
                return call_user_func($this->stringTransformer, $item, $i);
            }
        )
            ->join("\n");
    }
}
