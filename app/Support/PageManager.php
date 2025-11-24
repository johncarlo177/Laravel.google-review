<?php

namespace App\Support;

use App\Http\Controllers\PageController;
use App\Interfaces\TranslationManager;
use App\Models\Config;
use App\Models\Page;
use App\Support\System\Translation\ConfigTranslator;
use Exception;
use Illuminate\Support\Facades\Route;

class PageManager
{
    private ConfigTranslator $translator;
    private TranslationManager $translations;

    public function __construct()
    {
        $this->translator = new ConfigTranslator();

        $this->translations = app(TranslationManager::class);
    }

    public static function registerRoutes()
    {
        try {
            $pages = static::instance()->getPublishedPages(['slug']);

            foreach ($pages as $page) {
                Route::get($page->slug, [PageController::class, 'viewPage']);
            }
        } catch (Exception $ex) {
        }
    }

    public static function instance()
    {
        return new static;
    }

    public function getPublishedPages($columns = [])
    {
        $query = Page::where('published', true);

        if (!empty($columns)) {
            foreach ($columns as $column) {
                $query->addSelect($column);
            }
        }

        return $query->get();
    }

    private function getTypeUrl($configKey)
    {
        $translatedUrl = $this->translator->translateLine(
            $configKey,
            ''
        );

        if (empty($translatedUrl)) {
            return Config::get($configKey);
        }

        return $translatedUrl;
    }

    public function getTypePages()
    {
        $types =
            Config::where('key', 'like', 'qrType%')
            ->get()
            ->reduce(function (
                $result,
                $configItem
            ) {

                $result[$configItem->key] = $this->getTypeUrl(
                    $configItem->key
                );

                return $result;
            }, []);

        return $types;
    }

    public static function renderQrTypeConfigsJsVariable()
    {
        if (frontend_custom_url()) {
            return '';
        }

        try {
            $instance = new static;

            $types = $instance->getTypePages();

            return sprintf('<script> window.QRCG_QR_TYPE_CONFIGS = %s; </script>', json_encode($types));
        } catch (Exception $ex) {
        }

        return '';
    }
}
