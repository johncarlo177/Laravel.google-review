<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Support\QRCodeTypes\ViewComposers\VCardPlus\VCardFileGenerator;

class VCardBlock extends LinkBlock
{
    public static function slug()
    {
        return 'vcard';
    }

    protected function shouldRender(): bool
    {
        return true;
    }

    protected function getContentText()
    {
        return $this->model->field('text', t('Add to contacts'));
    }

    public function script()
    {
        return VCardFileGenerator::withDataProvider(
            fn($key) => $this->model->field($key)
        )->script();
    }
}
