<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SuperUserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $data = $this->makeSuperUserData();

        $user = User::whereEmail($data['email'])->first();

        if (!$user)
            $user = new User();

        $user->forceFill($data);

        $user->save();

        $role = Role::whereName('Admin')->first();

        DB::insert(
            'insert into user_roles (user_id, role_id) values (?, ?)',
            [$user->id, $role->id]
        );
    }

    public function makeSuperUserData()
    {
        $fields = [
            'name', 'email', 'password'
        ];

        $envKeys = array_map(fn ($var) => 'SUPER_USER_' . strtoupper($var), $fields);

        $data = array_reduce(
            array_keys($envKeys),
            function ($result, $i) use ($fields, $envKeys) {
                $result[$fields[$i]] = env($envKeys[$i]);
                return $result;
            },
            []
        );

        $data = array_merge(
            $data,

            [
                'email_verified_at' => Carbon::now(),
            ]
        );

        $emptyEnvFields  = array_filter(
            $envKeys,
            fn ($key) => empty(env($key))
        );

        if (!empty($emptyEnvFields)) {
            $fieldNames = implode(', ', $emptyEnvFields);

            $verb = count($emptyEnvFields) > 1 ? 'are' : 'is';

            throw new \InvalidArgumentException("$fieldNames $verb not provided in .env file");
        }

        // Passwords should be hashed before saved to the database
        $data['password'] = Hash::make($data['password']);

        if ($data['password'] === env('SUPER_USER_PASSWORD')) {
            Log::error(
                sprintf(
                    'Database encryption failed. plain (%s), hashed (%s)',
                    env('SUPER_USER_PASSWORD'),
                    $data['password'],
                )
            );
        }

        return $data;
    }
}
