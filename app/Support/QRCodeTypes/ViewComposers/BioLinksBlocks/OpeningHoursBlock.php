<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssRuleGenerator;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssRuleItem;
use App\Support\QRCodeTypes\ViewComposers\Traits\HasBusinessHours;

class OpeningHoursBlock extends BaseBlock
{
    use HasBusinessHours;

    public static function slug()
    {
        return 'opening-hours';
    }

    protected function designValue($key)
    {
        return null;
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('openingHours');
    }

    public function blockStyles()
    {
        return CssRuleGenerator::withSelector(
            implode(', ', [
                $this->blockSelector('.white-card')
            ])
        )
            ->withModel($this->model)
            ->rule('background-color', 'background_color')
            ->rule('color', 'text_color')
            ->rule('padding', 'block_padding', 'px')
            ->generate();
    }

    public function titleStyles()
    {
        return $this
            ->select('.white-card header')
            ->rule('color', 'title_color')
            ->rule('font-weight', 'title_font_weight')
            ->rule('font-size', 'title_font_size', 'px')
            ->generate();
    }

    public function contentStyles()
    {
        return $this->select('.white-card .row')
            ->rule('font-size', 'content_font_size', 'px')
            ->rule('font-weight', 'content_font_weight')
            ->generate();
    }

    public function iconStyles()
    {
        return $this->select('.white-card header svg path')
            ->rule('fill', 'icon_color');
    }

    private function fetchOpeningHours()
    {
        return array_map(fn($f) => (object) $f, $this->model->field('openingHours', []));
    }
}
