<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\TextFontStyle;

class InformationPopupBlock extends BaseBlock
{
    public static function slug()
    {
        return 'information-popup';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('text');
    }

    protected function linkTextColorStyles()
    {
        $textColor = $this->model->field('textColor');

        if (empty($textColor)) {
            return;
        }

        return sprintf('%s .link { color: %s; }', $this->blockSelector(), $textColor);
    }

    protected function fontStyles()
    {
        return TextFontStyle::withSelector(
            $this->blockSelector() . ' .link',
        )
            ->withModel($this->model)
            ->generate();
    }

    public function popupDetails()
    {
        return [
            'popup' => [
                'title' => $this->model->field('title'),
                'text' => $this->model->field('text'),
                'content' => $this->model->field('content'),
            ]
        ];
    }
}
