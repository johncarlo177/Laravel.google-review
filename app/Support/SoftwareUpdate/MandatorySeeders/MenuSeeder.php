<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use App\Models\Config;

class MenuSeeder extends Seeder
{
    protected $version = 'v2.00';

    protected function run()
    {
        $menus = require(base_path('database/raw/menus.php'));

        foreach ($menus as $key => $value) {
            Config::set($key, $value);
        }
    }
}
