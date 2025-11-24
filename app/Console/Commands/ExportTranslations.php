<?php

namespace App\Console\Commands;

use App\Interfaces\TranslationManager;
use App\Models\Translation;
use Illuminate\Console\Command;

class ExportTranslations extends Command
{
    private TranslationManager $translations;

    public function __construct(TranslationManager $translations)
    {
        parent::__construct();

        $this->translations = $translations;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export translation records to database/raw/translations.php. Save language files database/raw/translation-{translationLocale}.json ';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->storageJsonIsNewer()) {
            return $this->error('Default translation file in database/raw directory is newer than the current file in the database.');
        }

        $this->deactivateAllTranslations();

        $this->activateEnglishTranslation();

        $translations = Translation::all();

        $translationArray = $translations->map(function ($translation) {
            $arr = $translation->toArray();

            unset($arr['translation_file_id']);

            unset($arr['file']);

            return $arr;
        })->toArray();

        file_put_contents(
            'database/raw/translations.php',
            sprintf('<?php return %s;',  var_export($translationArray, true))
        );


        $translations->each(function ($translation) {
            $languageFile = $this->translations->load($translation);

            file_put_contents("database/raw/translation-{$translation->locale}.json", $languageFile);
        });

        $this->info('Translations exported successfully');

        return 0;
    }

    private function storageJsonIsNewer()
    {
        $lastModify = filemtime(base_path('database/raw/translation-en.json'));

        /** @var \Carbon\Carbon */
        $updated_at = Translation::whereLocale('en')->first()->updated_at;

        return $lastModify > $updated_at->timestamp;
    }

    private function activateEnglishTranslation()
    {
        $translation = Translation::whereLocale('en')->first();

        /** @var TranslationManager */
        $manager = app(TranslationManager::class);

        $manager->activate($translation);
    }

    private function deactivateAllTranslations()
    {
        $translations = Translation::all();

        $translations->each(function (Translation $translation) {
            /** @var TranslationManager */
            $manager = app(TranslationManager::class);

            $manager->deActivate($translation);
        });
    }
}
