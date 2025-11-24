<?php

namespace App\Console\Commands;

use App\Models\Config;
use Illuminate\Console\Command;

class ExportMenus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:menus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports default menus';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $keys = ['app.website-header-menu', 'app.website-footer-menu', 'dashboard.client-menu'];

        $export = [];

        foreach ($keys as $key) {
            $export[$key] = Config::get($key);
        }

        $file = base_path('database/raw/menus.php');

        $data = var_export($export, true);

        file_put_contents($file, "<?php return $data;");

        $this->info(sprintf('%s menus exported successfully.', count($export)));

        return 0;
    }
}
