<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\TextFontStyle;

class CopyableDataBlock extends BaseBlock
{
    public static function slug()
    {
        return 'copyable-data';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('label') && $this->model->notEmpty('value');
    }

    protected function labelStyles()
    {
        return $this->select('.label')
            ->rule('color', 'label_color')
            ->generate();
    }

    protected function labelFontStyles()
    {
        return TextFontStyle::withSelector($this->blockSelector('.label'))
            ->withModel($this->model)
            ->withPrefix('label')
            ->generate();
    }

    protected function valueStyles()
    {
        return $this->select('.value')
            ->rule('color', 'value_color')
            ->generate();
    }

    protected function valueFontStyles()
    {
        return TextFontStyle::withSelector($this->blockSelector('.value .text'))
            ->withModel($this->model)
            ->withPrefix('value')
            ->generate();
    }

    protected function blockStyles()
    {
        return $this->select()
            ->rule('border-radius', 'borderRadius', 'rem')
            ->rule('background-color', 'background_color')
            ->rule('padding', 'padding', 'rem')
            ->rule('border-color', 'border_color')
            ->generate();
    }
}
