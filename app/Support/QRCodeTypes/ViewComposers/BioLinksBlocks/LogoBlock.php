<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

/**
 * @deprecated 
 * @use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\ProfileBlock
 */
class LogoBlock extends BaseBlock
{
    public static function slug()
    {
        return 'logo';
    }

    protected function shouldRender(): bool
    {
        return false;
    }

    protected function textColorStyles()
    {
        if ($this->model->empty('textColor')) return;

        return sprintf(
            '%s .handle { color: %s; }',
            $this->blockSelector(),
            $this->model->field('textColor')
        );
    }
}
