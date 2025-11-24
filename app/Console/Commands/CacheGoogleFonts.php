<?php

namespace App\Console\Commands;

use App\Support\GoogleFonts;
use Illuminate\Console\Command;

class CacheGoogleFonts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:google-fonts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache all Google fonts to the local file system.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fonts = new GoogleFonts();

        $total = $fonts->countVariants();

        $this->info('Total files to be cached: ' . $total);

        $number = 0;

        foreach ($fonts->listFamilies() as $font) {
            foreach ($font['variants'] as $variant) {
                $number++;
                $fonts->getFontFile($font['family'], $variant);
                $this->info(sprintf('Cached file %s Progress: %s%%', $number, floor($number / $total * 100)));
            }
        }

        $this->info('All done.');

        return 0;
    }
}
