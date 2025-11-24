<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->seedLocal();
    }

    public function seedLocal()
    {
        $this->call([
            SubscriptionPlanSeeder::class,
            UserSeeder::class,
            QRCodeSeeder::class,
            QRCodeScanSeeder::class,
            TransactionSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            UserRolesSeeder::class,
            RolePermissionsSeeder::class,
            BlogPostSeeder::class,
            ContentBlockSeeder::class,
            TranslationSeeder::class,
        ]);
    }

    public function seedProduction()
    {
        $seeders = [
            SubscriptionPlanSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionsSeeder::class,
            SuperUserSeeder::class,
            BlogPostSeeder::class,
            ContentBlockSeeder::class,
            TranslationSeeder::class,
        ];

        foreach ($seeders as $seeder) {
            try {
                $this->call($seeder);
            } catch (\Throwable $th) {
                Log::error("$seeder execution failed");
                Log::error($th->getMessage());
            }
        }
    }
}
