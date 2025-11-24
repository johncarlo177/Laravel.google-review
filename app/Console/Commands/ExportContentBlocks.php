<?php

namespace App\Console\Commands;

use App\Models\ContentBlock;
use Illuminate\Console\Command;

class ExportContentBlocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:content-blocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export content blocks database records to database/raw/content-blocks.php';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $blocks = ContentBlock::all()->map(function ($block) {
            $block->translation_id = null;
            return $block;
        })->toArray();

        file_put_contents(
            base_path('database/raw/content-blocks.php'),
            sprintf('<?php return %s;',  var_export($blocks, true))
        );

        $this->info(sprintf('%s content blocks exported successfully.', count($blocks)));

        return 0;
    }
}
