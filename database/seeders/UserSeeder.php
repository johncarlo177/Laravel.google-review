<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Support\System\Traits\WriteLogs;

class UserSeeder extends Seeder
{
    use WriteLogs;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->logDebug('User seeder ');

        User::factory()->count(1)->admin()->create();
    }
}
