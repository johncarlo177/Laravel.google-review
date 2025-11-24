<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use App\Models\CustomCode;

class CustomCodeSeeder extends Seeder
{
    protected $rawFile = 'custom-codes';

    protected $version = '1.19.3/1';

    protected function shouldInsertRow(array $row)
    {
        return true;
    }

    protected function newModel($row)
    {
        return new CustomCode();
    }
}
