<?php

namespace App\Support\System\Translation;

use App\Interfaces\TranslationManager;
use App\Models\MetaItem;

/**
 * Translates a field in any model
 */
class LineTranslator
{
    public function saveLine($modelClass, $modelId, $field, $locale, $text)
    {
        $modelClass = sprintf('\App\Models\%s', $modelClass);

        /** @var \Illuminate\Database\Eloquent\Model */
        $instance = $modelClass::find($modelId);

        return $instance->setMeta(
            sprintf(
                'translation.%s.%s',
                $locale,
                $field
            ),
            $text
        );
    }

    public function getLines($modelClass, $modelId, $field)
    {
        $modelClass = sprintf('\App\Models\%s', $modelClass);

        $instance = $modelClass::findOrFail($modelId);

        $items = MetaItem::where('related_model', $instance::class)
            ->where('related_model_id', $instance->id)
            ->where('key', 'like', 'translation%')
            ->get();

        return $items->filter(function ($item) use ($field) {
            return preg_match("/translation\..*\.$field/", $item->key);
        })->values();
    }

    public function translateLine($model, $field)
    {
        /** @var TranslationManager */
        $translations = app(TranslationManager::class);

        $translation = $translations->getCurrentTranslation();

        $translated = $model->getMeta(
            sprintf('translation.%s.%s', $translation->locale, $field)
        );

        if (!empty($translated)) {
            return $translated;
        }

        return @$model->{$field};
    }
}
