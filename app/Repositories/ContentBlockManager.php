<?php

namespace App\Repositories;

use App\Http\Requests\StoreContentBlockRequest;
use App\Http\Requests\UpdateContentBlockRequest;
use App\Interfaces\ContentBlockManager as ContentBlockManagerInterface;
use App\Interfaces\ModelSearchBuilder;
use App\Interfaces\TranslationManager;
use App\Models\ContentBlock;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ContentBlockManager implements ContentBlockManagerInterface
{
    private ModelSearchBuilder $search;
    private TranslationManager $translations;

    public function __construct(ModelSearchBuilder $search)
    {
        $this->search = $search;
        $this->translations = app(TranslationManager::class);
    }

    public function list()
    {
        return $this->search
            ->init(ContentBlock::class, request(), false)
            ->inColumn('title')
            ->inColumn('position')
            ->withQuery(function ($query) {
                $query->with('translation');
                $query->orderBy('translation_id', 'asc');
                $query->orderBy('sort_order', 'asc');
            })
            ->withQuery(function (Builder $query) {
                if (request()->translation_id) {
                    $query->where('translation_id', request()->translation_id);
                }
            })
            ->search()
            ->paginate();
    }

    public function get(ContentBlock $block)
    {
        return $block;
    }

    public function store(StoreContentBlockRequest $request)
    {
        $contentBlock = new ContentBlock($request->all());

        $contentBlock->save();

        $this->clearCache();

        return $contentBlock;
    }

    public function update(UpdateContentBlockRequest $request, ContentBlock $block)
    {
        $block->fill($request->all());

        $block->save();

        $this->clearCache();

        return $block;
    }

    private function clearCache()
    {
        Artisan::call('view:clear');
    }

    public function getCountOfContentBlocksOfTranslation(Translation $translation)
    {
        return ContentBlock::where('translation_id', $translation->id)->count();
    }

    public function importContentBlocksToTranslation(Translation $translation)
    {
        if ($this->getCountOfContentBlocksOfTranslation($translation) == 0) {
            $this->importAllContentBlocksToTranslation($translation);
        } else {
            $this->importNewPositionsToTranslation($translation);
        }
    }

    private function importNewPositionsToTranslation(Translation $translation)
    {
        $defaultTranslation = $this->translations->getDefaultTranslation();

        $blocks = ContentBlock::where(
            'translation_id',
            $defaultTranslation->id
        )
            ->get();

        foreach ($blocks as $block) {

            $isFound = ContentBlock::where('translation_id', $translation->id)
                ->where('position', $block->position)
                ->first();

            if ($isFound) continue;

            $clone = new ContentBlock($block->toArray());
            $clone->translation_id = $translation->id;
            $clone->save();
        }
    }

    private function importAllContentBlocksToTranslation(Translation $translation)
    {
        $defaultTranslation = $this->translations->getDefaultTranslation();

        $blocks = ContentBlock::where('translation_id', $defaultTranslation->id)->get();

        foreach ($blocks as $block) {
            $clone = new ContentBlock($block->toArray());
            $clone->translation_id = $translation->id;
            $clone->save();
        }
    }

    public function assignAllBlocksWithNoTranlsationToEnglish()
    {
        $defaultEnglish = $this->translations->getDefaultTranslation();

        DB::table('content_blocks')
            ->whereNull('translation_id')
            ->update([
                'translation_id' => $defaultEnglish->id,
            ]);
    }


    public function destroy(ContentBlock $block)
    {
        $block->delete();

        return $block;
    }

    public function destroyAllOfTranslation($translationId)
    {
        ContentBlock::where('translation_id', $translationId)->delete();

        return [
            'success' => true
        ];
    }

    public function copyContentBlocks(Translation $from, Translation $to)
    {
        $blocks = ContentBlock::where('translation_id', $from->id)->get();

        foreach ($blocks as $block) {
            $clone = new ContentBlock($block->toArray());
            $clone->translation_id = $to->id;
            $clone->save();
        }

        return [
            'count' => $blocks->count()
        ];
    }
}
