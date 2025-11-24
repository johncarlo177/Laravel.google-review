<?php

namespace App\Support\ViewComposers;

use App\Support\System\Translation\ConfigTranslator;
use Throwable;

class WebsiteFooterComposer extends BaseComposer
{
    public static function path(): string
    {
        return 'blue.partials.footer';
    }

    public function groupName($group, $gi)
    {
        if (!is_array($group)) {
            return '';
        }

        if (!$this->translations->multilingualEnabled()) {
            return $group['name'];
        }

        $path = sprintf('%s.name', $gi);

        $translator = new ConfigTranslator();

        $translated = $translator->translateLine($this->menuKey(), $path);


        return empty(trim($translated)) ? $group['name'] : $translated;
    }

    public function itemLink($item, $i, $gi)
    {
        if (!$this->translations->multilingualEnabled()) {
            return @$item['link'];
        }

        $path = sprintf('%s.items.%s.link', $gi, $i);

        $translator = new ConfigTranslator();

        $translated = $translator->translateLine($this->menuKey(), $path);

        return empty(trim($translated)) ? $item['link'] : $translated;
    }

    public function label($item, $i, $gi)
    {
        if (!$this->translations->multilingualEnabled()) {
            return $item['label'];
        }

        $path = sprintf('%s.items.%s.label', $gi, $i);

        $translator = new ConfigTranslator();

        $translated = $translator->translateLine($this->menuKey(), $path);

        return empty(trim($translated)) ? $item['label'] : $translated;
    }

    private function menuKey()
    {
        return 'app.website-footer-menu';
    }

    public function menu()
    {
        $menu = config('app.website-footer-menu');

        if (empty($menu) || !is_array($menu)) {
            $menu = [];
        }

        return $menu;
    }
}
