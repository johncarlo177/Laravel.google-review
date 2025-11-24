<?php

namespace App\Interfaces;

use App\Models\Translation;
use Illuminate\Http\Request;

interface TranslationManager
{
    /**
     * Translate the given text into active system wide language. 
     */
    public static function t(string $text);

    public function search(Request $request);

    public function getActiveTranslations();

    public function save($data, ?Translation $translation = null);

    public function delete(Translation $translation);

    public function load(Translation $translation);

    public function loadTranslationArray(Translation $translation): array;

    /**
     * Updates the translation file with given @param data
     */
    public function write(string $data, Translation $translation);

    public function writeTranslationArray(array $data, Translation $translation);

    public function completeness(Translation $trasnlation);

    public function verifyTranslationFile(Translation $translation);

    public function upload(Request $request, Translation $translation);

    public function activate(Translation $translation);

    public function deActivate(Translation $translation);

    public function toggleActivate(Translation $translation);

    public function setMain(Translation $translation);

    public static function loadCurrentTranslationFile();

    public function setTranslationForThisRequest(Translation $translation);

    public function setCurrentTranslation($locale);

    /**
     * @return Translation
     */
    public function getCurrentTranslation();

    public function multilingualEnabled(): bool;

    public function getDefaultTranslation(): Translation;
}
