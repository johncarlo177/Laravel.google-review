<?php

namespace App\Repositories;

use App\Events\TranslationActivated;
use App\Interfaces\FileManager;
use App\Interfaces\ModelSearchBuilder;
use App\Interfaces\TranslationManager as TranslationManagerInterface;
use App\Models\Translation;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use App\Interfaces\UserManager as UserManagerInterface;

class TranslationManager implements TranslationManagerInterface
{
    use WriteLogs;

    private ModelSearchBuilder $search;

    private FileManager $files;

    private UserManagerInterface $users;

    private static $activeTranslation;

    private static $currentTranslation = null;

    private static $didFetchCurrentTranslation = false;

    private static $triedToGetCurrentTranslation = false;

    private static $multilingualEnabled = null;

    private $disableActivateInDemo = true;

    public function __construct(ModelSearchBuilder $search, FileManager $files)
    {
        $this->search = $search;

        $this->files = $files;

        $this->users = app(UserManagerInterface::class);
    }

    public function setTranslationForThisRequest(Translation $translation)
    {
        static::$currentTranslation = $translation;
    }

    public function setCurrentTranslation($locale)
    {
        $translation = Translation::whereLocale($locale)
            ->where('is_active', true)->first();

        if (!$translation) abort(404);

        $this->logDebug('setting locale to %s', $locale);

        // 10 years expires
        cookie()->queue('locale', $locale, time() + (10 * 365 * 24 * 60 * 60));

        if (cookie()->queued('locale')) {
            $this->logDebug('queued locale = %s', cookie()->queued('locale'));
        }
    }

    private static function loadCurrentTranslation()
    {
        if (!config('app.installed')) return;

        if (static::$triedToGetCurrentTranslation) return;

        if (static::$activeTranslation) return;

        try {
            $data = static::loadCurrentTranslationFile();

            static::$activeTranslation = json_decode($data, true);
        } catch (\Throwable $th) {
        }

        static::$triedToGetCurrentTranslation = true;
    }

    public static function t($text)
    {
        static::loadCurrentTranslation();

        if (!is_string($text)) {
            return $text;
        }

        if (!empty(static::$activeTranslation[$text])) {
            return static::$activeTranslation[$text];
        }

        return $text;
    }

    public function getActiveTranslations()
    {
        return Translation::where('is_active', true)->get();
    }

    public function search(Request $request)
    {
        $paginate = true;

        if ($request->paginate === 'false') {
            $paginate = false;
        }

        $search = $this->search->init(
            Translation::class,
            $request
        )
            ->inColumn('name')
            ->search();

        if ($request->boolean('is_active')) {
            $search->query()->where('is_active', true);
        }

        if ($paginate) {
            return $search->paginate()
                ->through(function ($row) {
                    $row->completeness = $this->completeness($row);
                    return $row;
                });
        }

        return $search->query()->get();
    }

    public function load(Translation $translation)
    {
        if (!$translation->file) {
            $this->createNewJsonFile($translation);
        }

        $text = $this->files->raw($translation->file);

        return empty($text) ? '[]' : $text;
    }

    private function createNewJsonFile(Translation $translation)
    {
        $adminId = $this->users->getSuperAdmins()[0]->id;

        $this->files->save(
            name: sprintf('translation-%s.json', $translation->locale),
            type: FileManager::FILE_TYPE_TRANSLATION,
            mime_type: 'text/plain;charset=UTF-8',
            attachable_type: $translation::class,
            attachable_id: $translation->id,
            user_id: $adminId,
            extension: 'json',
            data: '{}'
        );

        $translation->refresh();
    }

    public function loadTranslationArray(Translation $translation): array
    {
        return json_decode($this->load($translation), true);
    }

    /**
     * Updates the translation file with $data
     */
    public function write(string $data, Translation $translation)
    {
        $this->files->write($translation->file, $data);

        $translation->touch();
    }

    public function writeTranslationArray(array $data, Translation $translation)
    {
        return $this->write(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), $translation);
    }

    public function save($data, ?Translation $translation = null)
    {
        if ($translation?->is_default) {
            // only allow filling flag_file_id for default translation
            $data = [
                'flag_file_id' => @$data['flag_file_id']
            ];
        }

        if (!$translation) {
            $translation = new Translation();
        }

        $translation->fill($data);

        $translation->save();

        return $translation;
    }

    public function delete(Translation $translation)
    {
        if ($translation->is_default) return $translation;

        return $translation->delete();
    }

    public function completeness(Translation $translation)
    {
        if (!$translation->file) return 0;

        if ($translation->is_default) return 100;

        $json = $this->load($translation);

        $data = json_decode($json, true);

        if (empty($data)) {
            return 0;
        }

        $completed = array_reduce(
            $data,
            fn($count, $value) => empty($value) ? $count : $count + 1,
            0
        );

        return floor($completed / count($data) * 100);
    }

    public function verifyTranslationFile(Translation $translation)
    {
        $json = $this->load($translation);

        $data = json_decode($json, true);

        if (empty($data)) {
            throw new InvalidArgumentException("Translation file has invalid json data.");
        }

        $defaultTranslationJson = $this->loadDefaultTranslation();

        $defaultData = json_decode($defaultTranslationJson, true);

        $notFoundKeys = [];

        foreach (array_keys($defaultData) as $key) {
            $found = array_filter(array_keys($data), fn($dataKey) => $key === $dataKey);

            if (!$found) {
                $notFoundKeys[] = $key;
            }
        }

        if (!empty($notFoundKeys)) {
            throw new InvalidArgumentException(
                "The following translation keys was not found, please download the default English file and copy missing keys.\n- " . implode("\n- ", $notFoundKeys)
            );
        }
    }

    private function loadDefaultTranslation()
    {
        $defaultTranslation = Translation::where('is_default', true)->first();

        return $this->load($defaultTranslation);
    }

    public static function getCurrentTranslationLocale()
    {
        /**
         * @var TranslationManager
         */
        $instance = app(static::class);

        return $instance->getCurrentTranslation()?->locale;
    }

    /**
     * @return Translation
     */
    public function getCurrentTranslation()
    {
        if ($this::$didFetchCurrentTranslation) {
            return $this::$currentTranslation;
        }

        $translation = null;

        try {
            if ($locale = Cookie::get('locale')) {
                $translation = Translation::whereLocale($locale)
                    ->where('is_active', true)->first();
            }

            if (!$translation) {
                $translation = Translation::where('is_main', true)->first();

                if (!$translation) {
                    $translation = Translation::where('is_active', true)->first();
                }

                if (!$translation) {
                    $translation = Translation::where('is_default', true)->first();
                }
            }
        } catch (\Throwable $th) {
            //
        }

        $this::$currentTranslation = $translation;

        $this::$didFetchCurrentTranslation = true;

        return $this::$currentTranslation;
    }

    public static function loadCurrentTranslationFile()
    {
        try {
            /**
             * @var static
             */
            $manager = app(static::class);

            $translation = $manager->getCurrentTranslation();

            return $manager->load($translation);
        } catch (\Throwable $th) {

            if (config('app.installed'))
                Log::error($th->getMessage());

            return '{}';
        }

        return '{}';
    }

    public function upload(Request $request, Translation $translation)
    {
        $request->merge([
            'attachable_type' => Translation::class,
            'attachable_id' => $translation->id,
            'type' => FileManager::FILE_TYPE_TRANSLATION
        ]);

        $this->files->setFileValidator(function () use ($translation) {
            $translation->refresh();
            $this->verifyTranslationFile($translation);
        });

        $result = $this->files->store($request);

        return $result;
    }

    public function deActivate(Translation $translation)
    {
        if ($translation->is_active) {

            return $this->toggleActivate($translation);
        }
    }

    public function activate(Translation $translation)
    {
        if (!$translation->is_active) {

            return $this->toggleActivate($translation);
        }
    }

    public function toggleActivate(Translation $translation)
    {
        if (app()->environment() === 'demo' && $this->disableActivateInDemo) {
            return [
                'error' => 'Cannot activate translation in demo'
            ];
        }

        $translation->is_active = !$translation->is_active;

        if (!$translation->is_active) {
            $translation->is_main = false;
        }

        $translation->save();

        if ($translation->is_active) {
            event(new TranslationActivated($translation));
        }

        return $translation;
    }

    public function setMain(Translation $translation)
    {
        if (app()->environment() === 'demo' && $this->disableActivateInDemo) {
            return [
                'error' => 'Disabled in demo'
            ];
        }

        if (!$translation->is_active) {
            return [
                'error' => 'Translation must be activated first'
            ];
        }

        Translation::get()->each(function ($t) {
            $t->is_main = false;
            $t->save();
        });

        $translation->is_main = true;

        $translation->save();

        return $translation;
    }

    public function getDefaultTranslation(): Translation
    {
        return Translation::where('is_default', true)->first();
    }

    public function multilingualEnabled(): bool
    {
        if ($this::$multilingualEnabled !== null) {
            return $this::$multilingualEnabled;
        }

        $this::$multilingualEnabled = Translation::where('is_active', true)->count() > 1;

        return $this::$multilingualEnabled;
    }
}
