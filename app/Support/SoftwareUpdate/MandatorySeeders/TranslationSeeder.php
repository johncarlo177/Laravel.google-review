<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use App\Interfaces\FileManager;
use App\Interfaces\TranslationManager;
use App\Models\Translation;
use App\Support\System\Traits\WriteLogs;
use Database\Seeders\TranslationSeeder as SeedersTranslationSeeder;
use Illuminate\Support\Facades\Log;
use Throwable;

class TranslationSeeder extends Seeder
{
    use WriteLogs;

    protected $rawFile = 'translations';

    protected $table = 'translations';

    /** @var string software_version/seeder_version */
    protected $version = 'v2.137/1';

    private TranslationManager $translationManager;

    public function __construct(FileManager $files, TranslationManager $translationManager)
    {
        $this->translationManager = $translationManager;
    }

    /**
     * This will write any missing translation file.
     */
    protected function beforeRun()
    {
        try {
            $this->logDebug('Running database/seeders/TranslationSeeder ...');

            (new SeedersTranslationSeeder)->run(doNotInsertNewTranslations: true);

            $this->logDebug('Completed successfully.');
        } catch (Throwable $th) {
            $this->logError(
                'Error running Database\\Seeders\\TranslationSeeder ' . $th->getMessage()
            );

            $this->logDebug($th->getTraceAsString());
        }
    }

    // TODO save the json file after row insertions.
    protected function shouldInsertRow(array $row)
    {
        return Translation::where('locale', $row['locale'])->count() == 0;
    }

    protected function afterRun()
    {
        // Update default translation file on every software update
        $this->syncDefaultTranslationFile();

        $this->syncTranslationFiles();

        $this->changeDefaultLanguageNameToEnglish();

        $this->fillShortNames();
    }

    private function changeDefaultLanguageNameToEnglish()
    {
        $t = Translation::where('is_default', true)->first();

        if (!$t) return;

        $t->name = 'English (default)';

        $t->save();
    }

    private function fillShortNames()
    {
        $translations = Translation::all();

        foreach ($translations as $translation) {

            if ($translation->is_default) {

                $translation->display_name = 'EN';
            } else {
                $translation->display_name = mb_strtoupper(mb_substr($translation->name, 0, 2));
            }

            $translation->save();
        }
    }

    protected function syncTranslationFiles()
    {
        foreach (Translation::all() as $translation) {

            $content = @$this->rawFile("translation-$translation->locale.json");

            if (!empty($content)) {

                $this->syncTranslationFile($content, $translation);
            } else {
                $this->logDebugf('Raw file is empty for translation %s', $translation->locale);
            }
        }
    }

    private function syncDefaultTranslationFile()
    {
        $content = $this->rawFile('translation-en.json');

        $this->syncTranslationFile($content, Translation::where('is_default', true)->first());
    }

    private function syncTranslationFile(string $newContent, Translation $translation)
    {
        $this->logDebugf('Syncing translation file %s', $translation->locale);

        try {

            $newArray = json_decode($newContent, true);

            $currentArray = $this->translationManager->loadTranslationArray($translation);

            // Add missing keys from default translation to current translation
            foreach ($newArray as $key => $value) {
                if (!isset($currentArray[$key])) {
                    $currentArray[$key] = $value;
                }
            }

            // Remove any extra keys found in current array.
            foreach ($currentArray as $key => $value) {
                if (!isset($newArray[$key])) {
                    unset($currentArray[$key]);
                }
            }

            $this->translationManager->writeTranslationArray($currentArray, $translation);

            $this->logDebugf('Syncing completed');
        } catch (Throwable $ex) {
            Log::error('Could not sync translation file during the update. ' . $translation->locale);
            Log::error($ex->getMessage());
            Log::debug($ex->getTraceAsString());
        }
    }

    protected function newModel($row)
    {
        return new Translation;
    }
}
