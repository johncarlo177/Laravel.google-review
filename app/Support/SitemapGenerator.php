<?php

namespace App\Support;

use App\Models\BlogPost;
use App\Models\Page;
use Carbon\Carbon;

class SitemapGenerator
{
    protected $urls = [];

    public static function generate()
    {
        $instance = new static;

        return $instance->generateUrls();
    }

    public function generateUrls()
    {
        $views = [
            [
                'file' => 'blue/pages/home.blade.php',
                'url' => url('/')
            ]
        ];

        foreach ($views as $view) {
            $this->urls[] = $this->generateViewUrl($view['file'], $view['url']);
        }

        $posts = BlogPost::published()->get();

        foreach ($posts as $post) {
            $this->urls[] = [
                'url' => $post->url,
                'date' => $post->updated_at->format('Y-m-d')
            ];
        }

        /** @var \App\Support\PageManager */
        $pageManager = app(PageManager::class);

        $pages = $pageManager->getPublishedPages(['slug', 'updated_at']);

        foreach ($pages as $page) {
            $this->urls[] = [
                'url' => url($page->slug),
                'date' => $page->updated_at->format('Y-m-d')
            ];
        }

        $this->urls = collect($this->urls)->sortByDesc('date')->unique('url');

        return $this->urls;
    }

    protected function generateViewUrl($viewPath, $url)
    {
        $filePath = base_path('resources/views/' . $viewPath);

        return [
            'url' => $url,
            'date' => (new Carbon(filemtime($filePath)))->format('Y-m-d')
        ];
    }

    protected function getDynamicPageOfUrl($url)
    {
        $slug = str_replace(url('/'), '', $url);

        return Page::whereSlug($slug)->first();
    }
}
