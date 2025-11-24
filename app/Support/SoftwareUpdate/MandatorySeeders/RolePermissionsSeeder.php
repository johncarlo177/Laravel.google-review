<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionsSeeder as SeedersRolePermissionsSeeder;
use Database\Seeders\RoleSeeder;

class RolePermissionsSeeder extends Seeder
{
    protected $version = '2.128/1';

    protected function run()
    {
        $seeders = [
            RoleSeeder::class,
            PermissionSeeder::class,
            SeedersRolePermissionsSeeder::class
        ];

        foreach ($seeders as $seeder) {
            $seeder = new $seeder();

            $seeder->run();
        }
    }
}
