<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Plugins\PluginManager;
use App\Repositories\TranslationManager;
use App\Support\ContentManager;
use Illuminate\Support\Facades\Cache;

class HomePageController extends Controller
{

    protected function getHomePageView()
    {
        $defaultViewPath = 'blue.pages.home';

        $path = PluginManager::doFilter(
            PluginManager::FILTER_HOMEPAGE_PATH,
            $defaultViewPath
        );

        return $path;
    }

    public function __invoke()
    {
        if (Config::get('homepage.under-construction') == 'enabled') {
            return view('blue.others.under-construction-page');
        }

        if (Config::get('app.use_login_screen_as_home_page') === 'yes') {
            return redirect(url('/account/login'));
        }

        return $this->renderHomePage();
    }

    protected function makeHomePage()
    {

        return view($this->getHomePageView())->render();
    }

    protected function renderHomePage()
    {
        // Disable the cache in local environment.
        if (isLocal()) {
            return $this->makeHomePage();
        }

        return Cache::remember(
            $this::getCacheKey(),
            now()->addYear(),
            $this->makeHomePage(...)
        );
    }

    protected static function getCacheKey()
    {
        return 'homepage-cache.' . TranslationManager::getCurrentTranslationLocale();
    }

    public static function rebuildHomePageCache()
    {
        ContentManager::setBodyClass('path-base');

        Cache::forget(static::getCacheKey());
        //
        (new static)->renderHomePage();
    }
}
