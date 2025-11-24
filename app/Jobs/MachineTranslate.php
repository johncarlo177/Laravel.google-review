<?php

namespace App\Jobs;

use App\Interfaces\MachineTranslation;
use App\Interfaces\TranslationManager;
use App\Models\Translation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MachineTranslate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Translation $translation;

    private MachineTranslation $translator;

    private TranslationManager $manager;

    /**
     * Create a new job instance.
     * @param id translation id
     * @return void
     */
    public function __construct($id)
    {
        $this->translation = Translation::find($id);

        $this->translator = app(MachineTranslation::class);

        $this->manager = app(TranslationManager::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("MachineTranslate: translation strated ({$this->translation->locale}).");

        $this->translator->translateLanguage(
            $this->manager->load($this->translation),
            $this->translation->locale,
            function ($data) {
                $this->manager->write($data, $this->translation);
            }
        );

        Log::info("MachineTranslate: translation completed ({$this->translation->locale}).");
    }
}
