<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\TextFontStyle;

class ParagraphBlock extends BaseBlock
{
    public static function slug()
    {
        return 'paragraph';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('text');
    }

    protected function textColorStyles()
    {
        if ($this->model->empty('textColor')) return;

        return sprintf(
            '%s .text { color: %s; }',
            $this->blockSelector(),
            $this->model->field('textColor')
        );
    }

    protected function textStyles()
    {
        return TextFontStyle::withSelector($this->blockSelector('.text'))
            ->withModel($this->model)
            ->generate();
    }

    protected function blockStyles()
    {
        return $this->select()
            ->rule('border-radius', 'borderRadius', 'rem')
            ->rule('background-color', 'background_color')
            ->rule('padding', 'padding', 'rem')
            ->generate();
    }
}
