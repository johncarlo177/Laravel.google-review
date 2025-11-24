<?php

namespace App\Support;

use Illuminate\Support\Facades\Config;

class PageTitle
{
    public function makeTitle($title = '')
    {
        if (empty($title)) {
            return $this->configTitle();
        }

        return t($title) . ' | ' . $this->configTitle();
    }

    private function configTitle()
    {
        $title = Config::get('app.name');

        return $title;
    }
}
