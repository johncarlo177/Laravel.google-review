<?php

namespace App\Support\ViewComposers;

use App\Interfaces\BlogPostManager;
use App\Interfaces\CurrencyManager;
use App\Interfaces\FileManager;
use App\Models\BlogPost;
use App\Models\SubscriptionPlan;
use App\Support\System\Traits\WriteLogs;
use Illuminate\View\View;

class BlogListComposer extends BaseComposer
{
    use WriteLogs;

    private BlogPostManager $posts;

    public function __construct(BlogPostManager $posts)
    {
        $this->posts = $posts;
    }

    public static function path(): string
    {
        return 'blue.sections.blog-list';
    }

    public function compose(View $view)
    {
        $this->logDebug('public list is %s', $this->posts->publicList());

        $view->with('posts', $this->posts->publicList());
    }
}
