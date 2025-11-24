<?php

namespace App\Support\ViewComposers;

use App\Support\System\Traits\WriteLogs;
use App\Support\ViewComposers\SocialLinksComposer\Manager;

class SocialLinksComposer extends BaseComposer
{
    use WriteLogs;

    public static function path(): string
    {
        return 'blue.components.social-links';
    }

    public function socialLinks()
    {
        $data = $this->view->getData();

        if (!isset($data['urls'])) {
            return [];
        }

        $urls = $data['urls'];

        if (empty($urls) || !is_string($urls)) {
            return [];
        }

        $urls = explode("\n", $urls);

        return collect($urls)
            ->map(fn($url) => trim($url))
            ->map(fn($url) => strtolower($url))
            ->filter(fn($url) => !empty($url))
            ->map(function ($url) {
                return [
                    'socialId' => Manager::withUrl($url)->resolve(),
                    'url' => $url,
                ];
            })
            ->values();
    }
}
