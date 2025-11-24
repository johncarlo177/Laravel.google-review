<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssRuleGenerator;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\TextFontStyle;

class TitleBlock extends BaseBlock
{
    public static function slug()
    {
        return 'title';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('text');
    }

    protected function generateStyles()
    {
        return CssRuleGenerator::withSelector(
            sprintf('%s', $this->blockSelector())
        )
            ->withModel($this->model)
            ->rule('margin-top', 'margin-top', 'rem')
            ->rule('margin-bottom', 'margin-bottom', 'rem')
            ->rule('color', 'textColor')
            ->generate();
    }

    protected function fontStyles()
    {
        return TextFontStyle::withSelector(
            sprintf('%s h2', $this->blockSelector())
        )
            ->withModel($this->model)
            ->generate();
    }
}
