<?php

namespace App\Support\ViewComposers\StackComponent;

use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssRuleGenerator;
use App\Support\ViewComposers\BaseComposer;
use Illuminate\View\View;

class Composer extends BaseComposer
{
    public static function path(): string
    {
        return 'qrcode.components.stack-component';
    }

    public function compose(View $view)
    {
        $this->view = $view;

        $view->with('component', $this);
    }

    public function isEnabled()
    {
        return @$this->view->getData()['enabled'] === 'enabled';
    }

    public function items()
    {
        if (!$this->isEnabled()) return collect();

        return collect($this->view->getData()['stack_data'])
            ->map(function ($item) {
                return StackItem::fromArray($item);
            });
    }

    public function renderStyles()
    {
        return $this->items()->map(function (StackItem $item) {
            return CssRuleGenerator::withSelector('#' . $item->getCssId())
                ->rule('background-color', fn() => $item->background_color ?? 'black')
                ->rule('color', fn() => $item->text_color ?? 'white')
                ->generate();
        })->join("\n");
    }
}
