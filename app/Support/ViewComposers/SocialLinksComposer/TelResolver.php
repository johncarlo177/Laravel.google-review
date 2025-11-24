<?php

namespace App\Support\ViewComposers\SocialLinksComposer;

class TelResolver extends DefaultResolver
{
    public function guessSocialId($url)
    {
        if (!preg_match('/^tel:/i', $url)) {
            return null;
        }

        return 'phone';
    }
}
