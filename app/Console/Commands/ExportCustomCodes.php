<?php

namespace App\Console\Commands;

use App\Models\CustomCode;
use Illuminate\Console\Command;

class ExportCustomCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:custom-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports all available custom codes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $customCodes = CustomCode::all()->toArray();

        file_put_contents('database/raw/custom-codes.php', sprintf('<?php return %s;',  var_export($customCodes, true)));

        $this->info(sprintf('%s Custom Codes exported successfully.', count($customCodes)));

        return 0;
    }
}
