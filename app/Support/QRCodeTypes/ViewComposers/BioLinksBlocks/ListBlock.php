<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Models\File;
use App\Repositories\FileManager;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Renderers\IconRenderer;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssRuleGenerator;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\TextFontStyle;

class ListBlock extends BaseBlock
{
    public static function slug()
    {
        return 'list';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('items');
    }

    protected function duplicateBlockFiles(BlockModel $newModel)
    {
        $items = $this->model->field('items');

        foreach ($items as &$item) {
            // 
            $file = File::find($item['icon_file']);

            if (!$file) continue;

            $manager = new FileManager;

            $item['icon_file'] = $manager->duplicate($file)->id;
        }

        $newModel->setField('items', $items);
    }

    public function blockStyles()
    {
        return CssRuleGenerator::withSelector(
            $this->blockSelector('.list-card')
        )
            ->withModel($this->model)
            ->rule('background-color', 'background_color')
            ->rule('border-radius', 'border_radius', 'px')
            ->rule('padding', 'block_padding', 'px')
            ->generate();
    }

    public function listItemStyles()
    {
        return CssRuleGenerator::withSelector(
            $this->blockSelector('.list-card .item')
        )
            ->withModel($this->model)
            ->rule('color', 'text_color')
            ->generate();
    }

    public function listItemFontStyles()
    {
        return TextFontStyle::withSelector(
            $this->blockSelector('.list-card .item')
        )
            ->withModel($this->model)
            ->withPrefix('text')
            ->generate();
    }

    public function iconStyles()
    {
        return CssRuleGenerator::withSelector(
            $this->blockSelector('.list-card .icon')
        )
            ->withModel($this->model)
            ->rule('color', 'icon_color')
            ->rule('background-color', 'icon_background_color')
            ->rule('--icon-size', 'icon_size', 'px')
            ->generate();
    }

    public function iconBorderStyles()
    {
        $css = CssRuleGenerator::withSelector(
            $this->blockSelector('.list-card .icon')
        )
            ->withModel($this->model);

        switch ($this->model->field('icon_border_style')) {
            case 'round':
                // by default it's round, no need to do anything here.
                return '';

            case 'square':
                return $css->rule('border-radius', fn() => '0px');

            case 'none':
                return $css->rule('background-color', fn() => 'transparent');
        }
    }

    public function titleStyles()
    {
        return CssRuleGenerator::withSelector(
            $this->blockSelector('.main-title')
        )
            ->withModel($this->model)
            ->rule('color', 'title_color')
            ->generate();
    }

    public function titleFontStyles()
    {
        return TextFontStyle::withSelector(
            $this->blockSelector('.main-title')
        )
            ->withModel($this->model)
            ->withPrefix('title')
            ->generate();
    }

    public function icon($item)
    {
        return IconRenderer::withModel($this->model)
            ->withIcon(@$item['icon'])
            ->withCustomIconId(@$item['icon_file'])
            ->render();
    }
}
