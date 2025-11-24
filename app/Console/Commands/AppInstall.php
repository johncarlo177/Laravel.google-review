<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SuperUserSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AppInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install 
                {--force : Force command run without asking for user confirmation} 
                {--super-user : Only update super-user using the details found in .env file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the application';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function intro()
    {
        if ($this->option('super-user')) {
            return $this->info('Will update super user with the credentials found in .env file');
        }

        $this->info('This command will migrate the database');

        $this->newLine();

        $this->info('It will also create a super user with credentials found in .env file');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->intro();

        if (!$this->confirmed()) return 0;

        if ($this->option('super-user')) {
            $this->seedSuperUser();
        } else {
            $this->installApp();
        }

        return 0;
    }

    private function confirmed()
    {
        if ($this->option('force')) {
            return true;
        }

        return $this->confirm("Do you wish to continue?");
    }

    private function seedSuperUser()
    {
        $seeder = new SuperUserSeeder();

        $seeder->run();

        $this->info('Super user updated. You can now login with credentials found in .env file');
    }

    private function installApp()
    {
        $this->call('migrate', ['--force' => true]);

        try {
            $seeder = new DatabaseSeeder();

            $seeder->seedProduction();

            $this->info('Database seeded successfully');

            Log::info('Database seeded successfully');
        } catch (\Throwable $error) {
            $this->error($error->getMessage());

            Log::error($error->getMessage());

            $this->info('To restart the process, please execute the command ./artisan app:reinstall');
        }

        $this->attemptCronJobInstallation();
    }

    public function attemptCronJobInstallation()
    {
        try {
            $this->installCronJobs();
        } catch (\Throwable $error) {
            $this->error($error->getMessage());
            $this->info('Cronjobs installation failed');
            Log::error('Cronjobs installation failed', ['error' => $error->getMessage()]);
        }
    }

    public static function cronjobCommand()
    {
        $format = 'curl -k %s >/dev/null 2>&1';

        $command = sprintf($format, route('cron'));

        return $command;
    }

    public static function systemCronJobCommand()
    {
        $systemCron = sprintf('*/5 * * * * %s', static::cronjobCommand());

        return $systemCron;
    }


    public function installCronJobs()
    {
        $format = '(crontab -l 2>/dev/null; echo "%s") | crontab -';

        $command = sprintf($format, $this::systemCronJobCommand());

        $commands = [
            $command,
            'crontab -l',
            'crond',
            'ps -ef | grep cron | grep -v grep',
        ];

        foreach ($commands as $command) {
            $output = null;
            $result = null;

            Log::info('Executing command: ' . $command);

            try {
                exec($command, $output, $result);

                Log::info('Command execution result', compact('command', 'output', 'result'));
            } catch (\Throwable $err) {
                Log::error('Error while calling exec function, trying to execute command: ' . $command);
            }
        }
    }
}
