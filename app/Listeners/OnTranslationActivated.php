<?php

namespace App\Listeners;

use App\Events\TranslationActivated;
use App\Interfaces\ContentBlockManager;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class OnTranslationActivated implements ShouldQueue, ShouldBeUnique
{
    private ContentBlockManager $contentBlocks;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->contentBlocks = app(ContentBlockManager::class);
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\TranslationActivated  $event
     * @return void
     */
    public function handle(TranslationActivated $event)
    {
        Log::info('Translation is activated, importing content blocks to activated language');

        $this->contentBlocks->assignAllBlocksWithNoTranlsationToEnglish();

        $this->contentBlocks->importContentBlocksToTranslation($event->translation);
    }
}
