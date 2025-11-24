<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

class ExportPages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:pages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export pages database records to database/raw/pages.php';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pages = Page::all()->toArray();

        file_put_contents('database/raw/pages.php', sprintf('<?php return %s;',  var_export($pages, true)));

        $this->info(sprintf('%s pages exported successfully.', count($pages)));

        return 0;
    }
}
