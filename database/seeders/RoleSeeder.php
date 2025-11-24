<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'Admin',
                'super_admin' => true,
                'home_page' => '/dashboard/qrcodes'
            ],
            [
                'name' => 'Client',
                'home_page' => '/dashboard/qrcodes',
                'is_default_role_for_new_signup' => true
            ],
            [
                'name' => 'Sub User',
                'home_page' => '/dashboard/qrcodes',
            ],
            [
                'name' => 'Reseller',
                'home_page' => '/dashboard/qrcodes',
            ]
        ];

        foreach ($roles as $role) {
            $model = Role::where('name', $role['name'])->first();

            if (!$model)
                $model = new Role();

            $model->forceFill($role);
            $model->save();
        }
    }
}
