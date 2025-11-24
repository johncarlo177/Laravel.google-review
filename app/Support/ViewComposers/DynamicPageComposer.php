<?php

namespace App\Support\ViewComposers;

use App\Models\Page;
use App\Support\System\Traits\WriteLogs;
use Illuminate\View\View;
use Throwable;

class DynamicPageComposer extends BaseComposer
{
    use WriteLogs;
    private Page $page;

    public static function path(): string
    {
        return 'blue.pages.dynamic';
    }

    public function renderHtmlContent()
    {
        $content = $this->page->html_content;


        $partials = $this->getPartials($content);

        $content = $this->renderPartials($content, $partials);


        return $content;
    }

    private function renderPartials($content, $partials)
    {
        if (empty($partials)) return $content;

        foreach ($partials as $partial) {
            try {
                $content = preg_replace("/\[$partial]/", $this->renderPartial($partial), $content);
            } catch (Throwable $th) {
                // 
                $this->logWarning($th->getMessage());
            }
        }

        return $content;
    }

    private function renderPartial($path)
    {
        try {
            $html = view($path)->render();

            return $html;
        } catch (Throwable $th) {
            $this->logWarning($th->getMessage());
            return $path;
        }
    }

    private function getPartials($content)
    {
        $partials = [];

        try {
            preg_replace_callback('/\[(.*)]/', function ($matches) use (&$partials) {

                if (empty($matches)) return;

                if (isset($matches[1]) && !empty($matches[1])) {
                    $partials[] = $matches[1];
                }
            }, $content);
        } catch (Throwable $th) {
            // 
            $this->logWarning($th->getMessage());
        }

        return $partials;
    }



    public function compose(View $view)
    {
        parent::compose($view);

        $data = $view->getData();

        $this->page = $data['page'];
    }
}
