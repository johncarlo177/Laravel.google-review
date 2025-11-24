<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

class VideoBlock extends BaseBlock
{
    public static function slug()
    {
        return 'video';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('video');
    }

    protected function getFileKeys()
    {
        return [
            'video',
        ];
    }

    public function videoUrl()
    {
        return $this->model->fileUrl('video');
    }
}
