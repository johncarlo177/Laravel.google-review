<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

class ImageBlock extends BaseBlock
{
    public static function slug()
    {
        return 'image';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('image');
    }

    protected function getFileKeys()
    {
        return [
            'image',
            'file'
        ];
    }

    protected function descriptionStyles()
    {
        if ($this->model->empty('descriptionColor')) return;

        return sprintf(
            '%s { color: %s; }',
            $this->blockSelector('.description'),
            $this->model->field('descriptionColor')
        );
    }

    protected function textStyles()
    {
        if ($this->model->empty('textColor')) return;

        return sprintf(
            '%s { color: %s; }',
            $this->blockSelector('.caption'),
            $this->model->field('textColor')
        );
    }

    protected function borderStyles()
    {
        if ($this->model->empty('borderColor')) return;

        $this->logDebug('border-color %s', $this->model->field('borderColor'));

        return sprintf(
            '%s.has-border { background-color: %s; }',
            $this->blockSelector(),
            $this->model->field('borderColor')
        );
    }

    public function classString()
    {
        if ($this->model->field('borderEnabled') === 'disabled') {
            return '';
        }

        return 'has-border';
    }

    public function url()
    {
        if ($this->hasFile()) {
            return $this->fileUrl();
        }

        if ($this->model->notEmpty('url')) {
            return $this->model->field('url');
        }

        return null;
    }

    public function hasFile()
    {
        return $this->model->notEmpty('file') && $this->model->fileUrl('file');
    }

    public function fileUrl()
    {
        return $this->model->fileUrl('file');
    }
}
