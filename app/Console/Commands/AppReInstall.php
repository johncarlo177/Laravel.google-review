<?php

namespace App\Console\Commands;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;

class AppReInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reinstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets the database and seeds production data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('This command will reset the database. Will clear all database tables');

        $this->info('');

        $this->info('It will also create a super user with credentials found in .env file');

        $confirmed = $this->confirm("Do you wish to continue?");

        if (!$confirmed) {
            return 0;
        }

        $this->call('migrate:refresh', ['--force' => true]);

        $this->call('storage:link');

        try {
            $seeder = new DatabaseSeeder();
            $seeder->seedProduction();
        } catch (\Throwable $error) {
            $this->error($error->getMessage());

            $this->info('To restart the process, please execute the command ./artisan app:reinstall');
        }

        return 0;
    }
}
