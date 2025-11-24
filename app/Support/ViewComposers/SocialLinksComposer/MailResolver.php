<?php

namespace App\Support\ViewComposers\SocialLinksComposer;

class MailResolver extends DefaultResolver
{
    public function guessSocialId($url)
    {
        if (!preg_match('/^mailto:/i', $url)) {
            return null;
        }

        return 'email';
    }
}
