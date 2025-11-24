<?php

namespace App\Console\Commands;

use App\Events\ConfigChanged;
use Illuminate\Console\Command;

class FaviconSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'favicon:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync website favicons with saved configs.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $configs = [
            'app.name',
            'frontend.favicon-android-chrome-192x192.png',
            'frontend.favicon-android-chrome-512x512.png',
            'frontend.favicon-apple-touch-icon.png',
            'frontend.favicon-favicon-16x16.png',
            'frontend.favicon-favicon-32x32.png',
            'frontend.favicon-favicon.ico',
            'frontend.favicon-mstile-150x150.png',
            'frontend.browserconfig.tile_color',
            'frontend.favicon-safari-pinned-tab.svg',
            'frontend.mask-icon.color',
        ];


        ConfigChanged::fire($configs);

        $this->info('Favicons synced successfully.');

        return 0;
    }
}
