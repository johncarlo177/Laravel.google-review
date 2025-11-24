<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

class SocialLinksBlock extends BaseBlock
{
    public static function slug()
    {
        return 'social-links';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('socialLinks');
    }

    protected function iconsColorStyles()
    {
        if ($this->model->empty('iconsColor')) return;

        return sprintf(
            '%s svg path { fill: %s; }',
            $this->blockSelector(),
            $this->model->field('iconsColor')
        );
    }

    protected function backgroundColorStyles()
    {
        if ($this->model->empty('backgroundColor')) return;

        return sprintf(
            '%s a { background-color: %s; }',
            $this->blockSelector(),
            $this->model->field('backgroundColor')
        );
    }
}
