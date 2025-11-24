<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTranslationRequest;
use App\Http\Requests\UpdateTranslationRequest;
use App\Interfaces\FileManager;
use App\Interfaces\TranslationManager;
use App\Jobs\MachineTranslate;
use App\Models\Translation;
use App\Support\System\Traits\WriteLogs;
use App\Support\System\Translation\ConfigTranslator;
use App\Support\System\Translation\LineTranslator;
use Illuminate\Http\Request;


class TranslationController extends Controller
{
    use WriteLogs;

    private FileManager $files;

    private TranslationManager $translations;

    private LineTranslator $lineTranslator;

    private ConfigTranslator $configTranslator;

    public function __construct(
        TranslationManager $translations,
        LineTranslator $lineTranslator,
        ConfigTranslator $configTranslator
    ) {
        $this->translations = $translations;

        $this->lineTranslator = $lineTranslator;

        $this->configTranslator = $configTranslator;

        $this->files = app(FileManager::class);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->translations->search(
            $request
        );
    }

    public function activeTranslations()
    {
        $list = $this->translations->getActiveTranslations();

        return $list->map(
            fn (
                Translation $translation
            ) => $this->translationResponse($translation)
        );
    }

    public function changeLanguage($locale, Request $request)
    {
        $this->translations->setCurrentTranslation($locale);

        return redirect()->back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTranslationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTranslationRequest $request)
    {
        return $this->translations->save($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Translation  $translation
     * @return \Illuminate\Http\Response
     */
    public function show(Translation $translation)
    {
        return $translation;
    }

    private function translationResponse(Translation $translation)
    {
        $flag = $translation->getFlag();

        return [
            'is_default' => $translation->is_default,
            'display_name' => $translation->display_name,
            'locale' => $translation->locale,
            'flag_url' => $flag ? $this->files->url($flag) : null
        ];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTranslationRequest  $request
     * @param  \App\Models\Translation  $translation
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTranslationRequest $request, Translation $translation)
    {
        return $this->translations->save($request->all(), $translation);
    }

    public function saveLine(Request $request)
    {
        $modelClass = $request->modelClass;
        $modelId    = $request->modelId;
        $field      = $request->field;
        $locale     = $request->locale;
        $text       = $request->text;

        return $this->lineTranslator->saveLine($modelClass, $modelId, $field, $locale, $text);
    }

    public function getLines(Request $request)
    {
        $modelClass = $request->modelClass;
        $modelId    = $request->modelId;
        $field      = $request->field;

        return $this->lineTranslator->getLines($modelClass, $modelId, $field);
    }

    public function saveConfigLine(Request $request)
    {
        $path       = $request->input('path');
        $configKey  = $request->configKey;
        $text       = $request->text;
        $locale     = $request->locale;

        return $this->configTranslator->saveConfigLine($configKey, $path, $locale, $text);
    }

    public function getConfigLines(Request $request)
    {
        $path       = $request->input('path');
        $configKey  = $request->configKey;

        return $this->configTranslator->getConfigLines($configKey, $path);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Translation  $translation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Translation $translation)
    {
        return $this->translations->delete($translation);
    }

    public function toggleActivate(Translation $translation)
    {
        return $this->translations->toggleActivate($translation);
    }

    public function setMain(Translation $translation)
    {
        return $this->translations->setMain($translation);
    }

    public function upload(Translation $translation, FileManager $files, Request $request)
    {
        return $this->translations->upload($request, $translation);
    }

    public function autoTranslate(Translation $translation)
    {
        $completeness = $this->translations->completeness($translation);

        if ($completeness == 100) {
            return [
                'error' => 'Translation is already completed.'
            ];
        }

        dispatch(new MachineTranslate($translation->id));

        return [
            'started' => true
        ];
    }

    public function canAutoTranslate()
    {
        return [
            'enabled' => !empty(config('services.google.api_key'))
        ];
    }
}
