<?php

namespace Database\Seeders;

use App\Interfaces\FileManager;
use App\Models\File;
use App\Models\Translation;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Database\Seeder;


class TranslationSeeder extends Seeder
{
    use WriteLogs;
    private FileManager $files;

    public function __construct()
    {
        $this->files = app(FileManager::class);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($doNotInsertNewTranslations = false)
    {
        $translations = require __DIR__ . '/../raw/translations.php';

        $this->logDebug('Running...');

        $this->logDebug(
            sprintf(
                'Found %s translations. %s',
                count($translations),
                implode(
                    ', ',
                    array_map(fn ($t) => $t['locale'], $translations)
                )
            )
        );

        foreach ($translations as $translation) {

            $model = Translation::where('locale', $translation['locale'])->first();


            if (!$model && $doNotInsertNewTranslations) {
                $this->logDebug(
                    sprintf('Translation found (%s), skipping ...', $translation['locale'])
                );

                continue;
            }

            if (!$model) {

                $model = new Translation([
                    'name' => $translation['name'],
                    'locale' => $translation['locale']
                ]);

                $model->is_default = $translation['is_default'];

                $model->is_active = $translation['is_active'];

                $model->direction = $translation['direction'];

                $model->save();
            }

            $data = file_get_contents(__DIR__ . "/../raw/translation-{$model->locale}.json");

            $file = File::where('attachable_type', Translation::class)
                ->where('attachable_id', $model->id)
                ->first();

            if (!$file) {
                $file = $this->files->save(
                    name: "translation-{$model->locale}.json",
                    type: FileManager::FILE_TYPE_TRANSLATION,
                    mime_type: 'text/plain;charset=UTF-8',
                    attachable_type: Translation::class,
                    attachable_id: $model->id,
                    user_id: 1,
                    extension: 'json',
                    data: $data
                );
            }

            if (!$this->files->exists($file)) {

                $this->logDebug(
                    'Translation json file not found, writing it now. ' . $translation['locale']
                );

                $this->files->write($file, $data);
            } else {
                $this->logDebug(
                    'Translation json file found. ' . $translation['locale']
                );
            }
        }
    }
}
