<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

class YoutubeBlock extends BaseBlock
{
    public static function slug()
    {
        return 'youtube';
    }

    protected function shouldRender(): bool
    {
        return $this->model->notEmpty(
            'youtube_iframe'
        );
    }

    public function isYoutubeIframe()
    {

        $iframe = $this->model->field('youtube_iframe');

        return preg_match('#https://www.youtube.com/embed#', $iframe);
    }
}
