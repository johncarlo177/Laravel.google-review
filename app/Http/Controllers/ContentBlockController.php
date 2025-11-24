<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContentBlockRequest;
use App\Http\Requests\UpdateContentBlockRequest;
use App\Interfaces\ContentBlockManager;
use App\Models\ContentBlock;
use App\Models\Translation;

class ContentBlockController extends Controller
{

    private ContentBlockManager $contentBlockManager;

    public function __construct()
    {
        $this->contentBlockManager = app(ContentBlockManager::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ContentBlockManager $contentBlockManager)
    {
        return $contentBlockManager->list();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreContentBlockRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreContentBlockRequest $request, ContentBlockManager $contentBlockManager)
    {
        $result = $contentBlockManager->store($request);

        HomePageController::rebuildHomePageCache();

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ContentBlock  $contentBlock
     * @return \Illuminate\Http\Response
     */
    public function show(ContentBlock $contentBlock, ContentBlockManager $contentBlockManager)
    {
        return $contentBlockManager->get($contentBlock);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateContentBlockRequest  $request
     * @param  \App\Models\ContentBlock  $contentBlock
     * @return \Illuminate\Http\Response
     */
    public function update(
        UpdateContentBlockRequest $request,
        ContentBlock $contentBlock,
        ContentBlockManager $contentBlockManager
    ) {
        $result = $contentBlockManager->update($request, $contentBlock);

        HomePageController::rebuildHomePageCache();

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ContentBlock  $contentBlock
     * @return \Illuminate\Http\Response
     */
    public function destroy(ContentBlock $contentBlock, ContentBlockManager $contentBlockManager)
    {
        $result = $contentBlockManager->destroy($contentBlock);

        HomePageController::rebuildHomePageCache();

        return $result;
    }

    public function destroyAllOfTranslation($translationId)
    {
        if (!$translationId) {
            return [
                'success' => false
            ];
        }

        return $this->contentBlockManager->destroyAllOfTranslation($translationId);
    }

    public function copyContentBlocks(Translation $sourceTranslation, Translation $destinationTranslation)
    {
        return $this->contentBlockManager->copyContentBlocks($sourceTranslation, $destinationTranslation);
    }
}
