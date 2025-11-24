<?php

namespace App\Support\System\Translation;

use App\Interfaces\TranslationManager;
use App\Models\Config;
use App\Models\MetaItem;
use App\Support\System\MemoryCache;
use Illuminate\Support\Facades\Log;


/**
 * Translates a given path in any config key
 * because configs are json encoded data and might
 * have nested arrays
 */
class ConfigTranslator
{
    public function saveConfigLine($configKey, $path, $locale, $text)
    {
        $item = Config::where('key', $configKey)->first();

        if (!$item) {
            abort(404);
        }

        return $item->setMeta(
            sprintf(
                'translation-%s-%s',
                $locale,
                $path
            ),
            $text
        );
    }

    public function translateLine($configKey, $path)
    {
        return MemoryCache::remember(
            __METHOD__ . $configKey . $path,
            function () use ($configKey, $path) {
                $configItem = @Config::asArray()[$configKey];

                $id = @$configItem['id'];

                if (!$id) {
                    return;
                }

                /** @var TranslationManager */
                $translations = app(TranslationManager::class);

                $translation = $translations->getCurrentTranslation();

                if (!$translation) {
                    return;
                }

                $model = new Config();

                $model->id = $id;

                $translated = $model->getMeta(
                    sprintf('translation-%s-%s', $translation->locale, $path)
                );

                return $translated;
            }
        );
    }

    public function getConfigLines($configKey, $path)
    {
        $configItem = Config::where('key', $configKey)->first();

        if (!$configItem) {
            return [];
        }

        $items = MetaItem::where('related_model', $configItem::class)
            ->where('related_model_id', $configItem->id)
            ->where('key', 'like', 'translation%')
            ->get();

        return $items->filter(function ($item) use ($path) {
            return preg_match("/translation-.*-$path/", $item->key);
        })->values();
    }
}
