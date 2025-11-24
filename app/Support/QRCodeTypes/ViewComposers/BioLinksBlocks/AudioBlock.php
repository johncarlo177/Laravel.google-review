<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;


class AudioBlock extends BaseBlock
{
    public static function slug()
    {
        return 'audio';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('audio_file');
    }

    protected function getFileKeys()
    {
        return [
            'audio_file',
        ];
    }
}
