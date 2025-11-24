<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

class FileBlock extends LinkBlock
{
    public static function slug()
    {
        return 'file';
    }

    protected function getFileKeys()
    {
        return array_merge(
            parent::getFileKeys(),
            [
                'file'
            ]
        );
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('file');
    }
}
