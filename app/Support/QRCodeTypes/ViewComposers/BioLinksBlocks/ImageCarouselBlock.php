<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Models\File;
use App\Repositories\FileManager;
use App\Support\QRCodeTypes\ViewComposers\Components\ImageCarousel\Component as ImageCarousel;
use App\Support\ViewComposers\BaseComposer;

class ImageCarouselBlock extends BaseBlock
{
    public static function slug()
    {
        return 'image-carousel';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('image-carousel');
    }

    protected function duplicateBlockFiles(BlockModel $newModel)
    {
        $data = $this->model->field('image-carousel');

        $items = $data['items'];

        foreach ($items as &$item) {
            // 
            $file = File::find($item['image']);

            if (!$file) continue;

            $manager = new FileManager;

            $item['image'] = $manager->duplicate($file)->id;
        }

        $newModel->setField('image-carousel', $data);
    }

    public function render(BaseComposer $composer)
    {
        if (!$this->shouldRenderBlock()) return;

        return ImageCarousel::withData(
            $this->model->field('image-carousel')
        )->render();
    }
}
