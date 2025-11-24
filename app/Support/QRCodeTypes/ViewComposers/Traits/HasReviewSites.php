<?php

namespace App\Support\QRCodeTypes\ViewComposers\Traits;

use App\Support\ArrayHelper;

trait HasReviewSites
{
    public function getReviewSites()
    {
        $sites = $this->designValue('review_sites');

        if (!is_array($sites)) {
            return [];
        }

        ArrayHelper::sort($sites);

        return $sites;
    }
}
