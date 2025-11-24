<?php

namespace App\Support\ViewComposers;

use App\Interfaces\TranslationManager;

use Illuminate\View\View;

use Illuminate\Support\Facades\View as ViewFacade;

abstract class BaseComposer
{
    protected TranslationManager $translations;

    protected View $view;

    public static function register()
    {
        $templates = explode('|', static::path());

        foreach ($templates as $template) {
            ViewFacade::composer($template, static::class);
        }
    }

    public function __construct()
    {
        $this->translations = app(TranslationManager::class);
    }

    abstract static function path(): string;

    public function compose(View $view)
    {
        $this->view = $view;

        $this->initComposer();

        $view->with('composer', $this);
    }

    protected function initComposer()
    {
    }
}
