<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class UserRolesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        foreach ($users as $i => $user) {

            if ($i == 0)
                $role = Role::whereName('Admin')->first();
            else
                $role = Role::where('name', '<>', 'Admin')->get()->random();

            DB::insert(
                'insert into user_roles (user_id, role_id) values (?, ?)',
                [$user->id, $role->id]
            );
        }
    }
}
