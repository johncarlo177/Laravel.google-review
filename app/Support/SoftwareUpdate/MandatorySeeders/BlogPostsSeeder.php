<?php

namespace App\Support\SoftwareUpdate\MandatorySeeders;

use App\Models\BlogPost;

class BlogPostsSeeder extends Seeder
{
    protected $version = 'v2.0';

    protected $table = 'blog_posts';

    protected $rawFile = 'blog-posts';

    private static $hasPostsInitially = true;

    protected function shouldInsertRow(array $row)
    {
        if (BlogPost::count() === 0) {
            $this::$hasPostsInitially = false;
        }

        return !$this::$hasPostsInitially;
    }

    protected function newModel($row)
    {
        return new BlogPost();
    }
}
