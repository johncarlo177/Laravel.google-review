<?php

namespace App\Console\Commands;

use App\Support\MaxMind\MaxMindUpdater;
use Illuminate\Console\Command;

class UpdateMaxmindDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maxmind:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Maxmind Database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $maxmindUpdater = new MaxMindUpdater();

        $maxmindUpdater->update();

        $this->info('Maxmind database updated successfully');
        return 0;
    }
}
