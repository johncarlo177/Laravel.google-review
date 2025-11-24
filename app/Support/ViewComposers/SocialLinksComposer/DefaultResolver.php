<?php

namespace App\Support\ViewComposers\SocialLinksComposer;

use App\Support\System\Traits\WriteLogs;

class DefaultResolver
{
    use WriteLogs;

    public function priority()
    {
        return 10000;
    }

    private function getHostname($url)
    {
        $parts = parse_url($url);

        if (!isset($parts['host'])) {
            return null;
        }

        $host = $parts['host'];

        return $host;
    }

    private function mapSocialIdAlias($socialId)
    {
        $aliases = [
            'wa' => 'whatsapp',
            'u.wechat' => 'wechat',
            'music.apple' => 'apple',
            't' => 'telegram',
            'account.venmo' => 'venmo',
        ];

        if (collect(array_keys($aliases))->first(fn($alias) => $alias == $socialId)) {
            return $aliases[$socialId];
        }

        return $socialId;
    }

    private function removeExtension($host)
    {
        $host = str_replace('www.', '', $host);

        $parts = explode('.', $host);

        $extension = $parts[count($parts) - 1];

        $host = preg_replace("/\.$extension$/", '', $host);

        return $host;
    }

    public function guessSocialId($url)
    {
        $host = $this->getHostname($url);

        $socialId = $this->removeExtension($host);

        $socialId = $this->mapSocialIdAlias($socialId);

        if (!$this->socialIconFound($socialId)) {
            $socialId = 'web';
        }

        return $socialId;
    }

    public function resolve($url)
    {
        $id = $this->guessSocialId($url);

        return $id;
    }

    protected function socialIconFound($socialId)
    {
        return file_exists(
            base_path(
                "resources/views/blue/components/icons/social/$socialId.blade.php"
            )
        );
    }
}
