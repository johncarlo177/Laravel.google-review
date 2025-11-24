<?php

namespace App\Console\Commands;

use App\Http\Controllers\HomePageController;
use Illuminate\Console\Command;

class RebuildHomePageCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:home:rebuild';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the home page cache';

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
        HomePageController::rebuildHomePageCache();

        $this->info('HomePage cache rebuilt');

        return 0;
    }
}
