<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Support\Sms\Drivers\Manager;
use Illuminate\Console\Command;

class EnableSmsDriver extends Command
{
    protected $signature = 'sms:enable {driver}';

    protected $description = 'Enable an SMS driver';

    public function handle()
    {
        $driverSlug = $this->argument('driver');

        $manager = new Manager();
        $driver = $manager->find($driverSlug);

        if (!$driver) {
            $this->error("SMS driver '{$driverSlug}' not found!");
            $this->line('Available drivers:');
            foreach ($manager->list() as $d) {
                $this->line('  - ' . $d->slug());
            }
            return 1;
        }

        $driver->enable();
        
        // Clear config cache to ensure changes are reflected immediately
        Config::clearCache();
        
        $this->info("✓ SMS driver '{$driverSlug}' enabled!");
        
        // Verify it's enabled
        $manager = new Manager(); // Create new instance to get fresh driver
        $driver = $manager->find($driverSlug);
        if ($driver->isEnabled()) {
            $this->info('✓ Verified: Driver is enabled');
        } else {
            $this->warn('⚠ Warning: Driver may not be enabled. Try clearing cache: php artisan config:clear');
        }

        return 0;
    }
}

