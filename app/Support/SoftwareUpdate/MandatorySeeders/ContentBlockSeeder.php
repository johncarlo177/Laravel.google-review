<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use App\Interfaces\ContentBlockManager;
use App\Models\ContentBlock;
use Illuminate\Support\Facades\DB;

class ContentBlockSeeder extends Seeder
{
    protected $rawFile = 'content-blocks';

    protected $version = 'v2.12.6';

    private ContentBlockManager $contentBlockManager;

    public function __construct()
    {
        $this->contentBlockManager = app(ContentBlockManager::class);
    }

    protected function shouldInsertRow(array $row)
    {
        if ($row['position'] == 'Testimonials: list')
            return ContentBlock::wherePosition($row['position'])->count() < 8;

        return empty(ContentBlock::wherePosition($row['position'])->first());
    }

    protected function newModel($row)
    {
        return new ContentBlock();
    }

    protected function afterRun()
    {
        $this->contentBlockManager->assignAllBlocksWithNoTranlsationToEnglish();
    }
}
