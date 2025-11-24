<?php

namespace App\Support\ViewComposers;

use App\Interfaces\BlogPostManager;
use Illuminate\View\View;

class BlogPostComposer extends BaseComposer
{
    private BlogPostManager $posts;

    public function __construct(BlogPostManager $posts)
    {
        $this->posts = $posts;
    }

    public static function path(): string
    {
        return 'blue.pages.post';
    }

    public function compose(View $view)
    {
        $view->with(
            'post',
            $this->posts->bySlug(
                request()->route('post')
            )
        );
    }
}
