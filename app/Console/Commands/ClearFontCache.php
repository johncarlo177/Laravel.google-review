<?php

namespace App\Console\Commands;

use App\Support\GoogleFonts;
use Illuminate\Console\Command;

class ClearFontCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-fonts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Google Fonts cache';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fonts = new GoogleFonts();

        $fonts->clearFontCache();

        $this->info('Font cache cleared');

        return 0;
    }
}
