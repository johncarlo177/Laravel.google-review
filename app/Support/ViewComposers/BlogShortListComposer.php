<?php

namespace App\Support\ViewComposers;

use App\Interfaces\BlogPostManager;


use Illuminate\View\View;

class BlogShortListComposer extends BaseComposer
{
    private BlogPostManager $posts;

    public function __construct(BlogPostManager $posts)
    {
        $this->posts = $posts;
    }

    public static function path(): string
    {
        return 'blue.sections.blog-short-list';
    }

    public function compose(View $view)
    {
        $view->with('posts', $this->getPosts());
        parent::compose($view);
    }

    public function getPosts()
    {
        return $this->posts->getLatestPosts(3);
    }

    public function shouldRender()
    {
        return count($this->getPosts()) > 0;
    }
}
