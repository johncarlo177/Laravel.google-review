<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Support\System\Traits\WriteLogs;

class ProfileBlock extends BaseBlock
{
    use WriteLogs;

    public static function slug()
    {
        return 'profile';
    }

    protected function getFileKeys()
    {
        return [
            'profile_image',
            'background_image'
        ];
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('profile_image');
    }

    protected function profileImageStyles()
    {
        if ($this->model->empty('profile_image')) {
            return;
        }

        $rules = [
            sprintf(
                'background-image: url(%s)',
                $this->model->fileUrl('profile_image')
            ),
            sprintf(
                '--profile-image-width: %srem',
                $this->model->field('size', '5')
            ),
            sprintf(
                'border-radius: %s',
                $this->model->field('border_style') === 'circle' ? '50%' : 0
            ),
            sprintf(
                'margin-bottom: %srem',
                $this->model->field('margin_bottom', 0)
            ),

            sprintf(
                'margin-top: %srem',
                $this->model->field('margin_top', 0)
            ),
        ];

        return sprintf(
            '%s .image { %s; }',
            $this->blockSelector(),
            implode('; ', $rules)
        );
    }

    protected function backgroundImageStyles()
    {
        if ($this->model->empty('background_image')) {
            return;
        }

        $rules = [
            sprintf(
                'background-image: url(%s)',
                $this->model->fileUrl('background_image')
            ),
        ];

        return sprintf(
            '%s .background { %s; }',
            $this->blockSelector(),
            implode('; ', $rules)
        );
    }

    protected function textColorStyles()
    {
        if ($this->model->empty('textColor')) return;

        return sprintf(
            '%s .handle { color: %s; }',
            $this->blockSelector(),
            $this->model->field('textColor')
        );
    }
}
