<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = require_once(base_path('database/raw/blog-posts.php'));

        foreach ($data as $row) {
            $post = new BlogPost();
            $post->forceFill($row);
            $post->save();
        }
    }
}
