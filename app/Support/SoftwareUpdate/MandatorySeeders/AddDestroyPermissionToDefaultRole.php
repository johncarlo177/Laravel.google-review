<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;


use App\Models\Permission;
use App\Models\Role;

use Database\Seeders\PermissionSeeder;

class AddDestroyPermissionToDefaultRole extends Seeder
{
    /** @var string software_version/seeder_version */
    protected $version = 'v1.17.2';

    protected function run()
    {
        /** @var PermissionSeeder */
        $permissionSeeder = app(PermissionSeeder::class);

        $permissions = $permissionSeeder->generatePermissions(
            'QRCode',
            $permissionSeeder->methods(
                only: [
                    'destroy',
                    'destroy-any',
                ],
            ),
            displayName: 'QRCode',
            kebabName: 'qrcode',
        );

        $permissionSeeder->save($permissions);

        $defaultPermissions = Permission::whereIn('slug', [
            'qrcode.destroy',
        ])->get();

        $defaultRole = Role::where('is_default_role_for_new_signup', true)->first();

        foreach ($defaultPermissions as $permission) {
            $defaultRole->permissions()->save($permission);
        }
    }
}
