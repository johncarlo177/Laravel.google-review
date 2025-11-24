<?php

namespace App\Console\Commands;

use App\Interfaces\TranslationManager;
use App\Jobs\MachineTranslate;
use App\Models\Translation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class UpdateTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync translation-en.json to all languages and then auto translate them.';

    protected $files;

    /** @var TranslationManager */
    private $translationManager;

    /**
     * Create a new command instance.
     * @param Filesystem $files
     */
    public function __construct()
    {
        parent::__construct();

        $this->translationManager = app(TranslationManager::class);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (config('queue.default') !== 'sync') {
            $this->error('Can only be executed when QUEUE_CONNECTION=sync');
            return;
        }

        $this->updateTranslations();

        Artisan::call('translation:export');

        $this->info('All translations exported successfully.');

        return 0;
    }

    private function updateTranslations()
    {
        $this->saveDefaultTranslationFromDatabaseRawDir();

        $translations = Translation::get()->values();

        foreach ($translations as $translation) {
            if ($translation->is_default) continue;

            $this->syncKeys($translation);
        }

        $this->info('All translation keys are synced.');


        foreach ($translations as $i => $translation) {
            if ($translation->is_default) continue;
            // 

            dispatch(new MachineTranslate($translation->id));

            $this->info('');

            $this->info(
                sprintf(
                    '(%s out of %s) %s has been translated successfully.',
                    $i,
                    $translations->count() - 1,
                    $translation->name
                )
            );
        }

        $this->line('');
        return 0;
    }

    private function saveDefaultTranslationFromDatabaseRawDir()
    {
        $data = $this->loadDefaultTranslationJson();

        $translation = Translation::where('is_default', true)->first();

        $this->save($data, $translation);
    }

    private function loadDefaultTranslationJson()
    {
        $defaultJson = json_decode(file_get_contents(
            base_path('database/raw/translation-en.json')
        ), true);

        return $defaultJson;
    }

    private function syncKeys(Translation $translation)
    {
        $data = json_decode($this->translationManager->load($translation), true);

        $defaultData = $this->loadDefaultTranslationJson();

        // Add missing keys
        foreach ($defaultData as $key => $value) {
            if (!isset($data[$key])) $data[$key] = $value;
        }

        // Remove extra keys that are not found in the default translation
        foreach ($data as $key => $value) {
            if (!isset($defaultData[$key])) unset($data[$key]);
        }

        $this->save($data, $translation);
    }

    private function save(array $data, Translation $translation)
    {
        $this->translationManager->write(
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            $translation
        );
    }
}
