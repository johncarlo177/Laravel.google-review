<?php

namespace App\Support\ViewComposers\SocialLinksComposer;

class MapsResolver extends DefaultResolver
{
    public function guessSocialId($url)
    {
        if (!preg_match('/maps/i', $url)) {
            return null;
        }

        return 'map-marker';
    }
}
