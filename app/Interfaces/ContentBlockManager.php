<?php

namespace App\Interfaces;

use App\Http\Requests\StoreContentBlockRequest;
use App\Http\Requests\UpdateContentBlockRequest;
use App\Models\ContentBlock;
use App\Models\Translation;

interface ContentBlockManager
{
    public function list();

    public function store(StoreContentBlockRequest $request);

    public function get(ContentBlock $block);

    public function update(UpdateContentBlockRequest $request, ContentBlock $block);

    public function destroy(ContentBlock $block);

    public function destroyAllOfTranslation($translationId);

    public function getCountOfContentBlocksOfTranslation(Translation $translation);

    public function importContentBlocksToTranslation(Translation $translation);

    public function assignAllBlocksWithNoTranlsationToEnglish();

    public function copyContentBlocks(Translation $from, Translation $to);
}
