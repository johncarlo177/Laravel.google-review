<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

class CustomCodeBlock extends BaseBlock
{
    public static function slug()
    {
        return 'custom-code';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty('code');
    }

    private function language()
    {
        $language = collect([
            'css', 'javascript', 'html'
        ])->first(fn ($l) => $l === $this->model->field('language'));

        if (!$language) return 'html';

        return $language;
    }

    public function code()
    {
        switch ($this->language()) {
            case 'javascript':
                return sprintf('<script>%s</script>', $this->model->field('code'));

            case 'css':
                return sprintf('<style>%s</style>', $this->model->field('code'));

            default:
                return $this->model->field('code');
        }
    }
}
