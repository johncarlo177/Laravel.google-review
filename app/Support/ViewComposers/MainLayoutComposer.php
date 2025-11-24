<?php

namespace App\Support\ViewComposers;

use App\Interfaces\TranslationManager;
use App\Support\ContentManager;

class MainLayoutComposer extends BaseComposer
{
    const PATTERN_PWA_ROUTES = '^(dashboard|account|install|checkout).*';

    public function __construct()
    {
        $this->translations = app(TranslationManager::class);
    }

    public static function path(): string
    {
        return 'blue.layouts.main';
    }

    public function locale()
    {
        if (!config('app.installed')) return;

        return $this->translations->getCurrentTranslation()->locale;
    }

    public function direction()
    {
        if (!config('app.installed')) return;

        return $this->translations->getCurrentTranslation()->direction;
    }

    public function frontendHeadCustomCode()
    {
        $pattern = sprintf('/%s/', $this::PATTERN_PWA_ROUTES);

        if (preg_match($pattern, request()->path())) {
            return;
        }

        return (app(ContentManager::class))->customCode('Frontend: head before close');
    }
}
