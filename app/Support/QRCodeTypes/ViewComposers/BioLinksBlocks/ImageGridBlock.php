<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Models\File;
use App\Repositories\FileManager;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssRuleGenerator;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\TextFontStyle;
use App\Support\System\Traits\WriteLogs;

class ImageGridBlock extends BaseBlock
{
    use WriteLogs;

    public static function slug()
    {
        return 'image-grid';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('items');
    }

    protected function duplicateBlockFiles(BlockModel $newModel)
    {
        $items = $this->model->field('items');

        foreach ($items as &$item) {
            $file = File::find($item['image']);

            if (!$file) continue;

            $manager = new FileManager;

            $item['image'] = $manager->duplicate($file)->id;
        }

        $newModel->setField('items', $items);
    }

    protected function getItemLinkStyle($item)
    {
        return $this->select('#' . $item['id'])
            ->rule(
                'background-image',
                fn() => sprintf(
                    'url(%s)',
                    file_url($item['image'])
                )
            )->generate();
    }

    protected function linkStyles()
    {
        return collect($this->model->field('items'))
            ->map(
                $this->getItemLinkStyle(...)
            )->join("\n");

        return $generated;
    }

    protected function gridStyles()
    {
        return $this->select('.grid')
            ->rule('gap', 'grid_gap', 'px');
    }

    protected function titleFontStyles()
    {
        return TextFontStyle::withSelector('.title')
            ->withModel($this->model)
            ->withPrefix('title')
            ->generate();
    }

    protected function titleStyles()
    {
        return $this->select('.title')
            ->rule('color', 'title_color');
    }

    protected function blockStyles()
    {
        // select the block itself
        return $this->select()

            ->rule('background-color', function () {
                if ($this->model->field('background_type') != 'solid_color') {
                    return null;
                }

                return $this->model->field('background_color');
            })->rule('padding', function () {
                if ($this->model->field('background_type') != 'solid_color') {
                    return null;
                }

                return $this->model->field('padding');
            }, 'px')
            ->rule('border-radius', function () {
                if ($this->model->field('background_type') != 'solid_color') {
                    return null;
                }

                return $this->model->field('border-radius');
            }, 'px');
    }
}
