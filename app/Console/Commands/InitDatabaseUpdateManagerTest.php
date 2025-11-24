<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InitDatabaseUpdateManagerTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db_update_test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Init database update end to end test scenario.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (app()->environment('production')) {
            $this->error('This command cannot run in production');
            return 1;
        }

        $commands = [
            'migrate:rollback --step 1000',
            'migrate',
            'db:seed RoleSeeder',
            'db:seed PermissionSeeder',
            'db:seed RolePermissionsSeeder',
            'db:seed SuperUserSeeder',
        ];

        foreach ($commands as $command) {

            $this->line("Calling: $command ...");

            Artisan::call($command);

            $this->info("Success: $command");
        }

        $this->info('Database test case scenario is set up successfully.');

        return 0;
    }
}
