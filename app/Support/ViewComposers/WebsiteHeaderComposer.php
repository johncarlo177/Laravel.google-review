<?php

namespace App\Support\ViewComposers;

use App\Support\Auth\Auth0\Auth0Manager;
use App\Support\System\Traits\WriteLogs;
use App\Support\System\Translation\ConfigTranslator;
use Throwable;

class WebsiteHeaderComposer extends BaseComposer
{
    use WriteLogs;

    private Auth0Manager $auth0;

    public function __construct()
    {
        parent::__construct();

        $this->auth0 = new Auth0Manager;
    }

    public static function path(): string
    {
        return 'blue.partials.header';
    }

    public function loginUrl()
    {
        if ($this->auth0->isEnabled()) {
            return $this->auth0::loginUrl();
        }

        return '/account/login';
    }

    public function registerUrl()
    {
        if ($this->auth0->isEnabled()) {
            return $this->auth0::loginUrl();
        }

        return '/account/sign-up';
    }

    public function itemLink($item, $i)
    {
        if (!$this->translations->multilingualEnabled()) {
            return $item['link'];
        }

        $path = sprintf('0.items.%s.link', $i);

        $translator = new ConfigTranslator();

        $translated = $translator->translateLine($this->menuKey(), $path);

        return empty(trim($translated)) ? $item['link'] : $translated;
    }

    public function label($item, $i)
    {
        if (!$this->translations->multilingualEnabled()) {
            return $item['label'];
        }

        $path = sprintf('0.items.%s.label', $i);

        $translator = new ConfigTranslator();

        $translated = $translator->translateLine($this->menuKey(), $path);

        return empty(trim($translated)) ? $item['label'] : $translated;
    }

    private function menuKey()
    {
        return 'app.website-header-menu';
    }

    public function menu()
    {
        $menu = config($this->menuKey());

        $items = [];

        try {
            $items = @$menu[0]['items'] ?? [];
        } catch (Throwable $th) {
        }

        return $items;
    }
}
