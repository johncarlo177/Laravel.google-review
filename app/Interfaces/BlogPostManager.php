<?php

namespace App\Interfaces;

use App\Http\Requests\StoreBlogPostRequest;
use App\Http\Requests\UpdateBlogPostRequest;
use App\Models\BlogPost;
use Illuminate\Http\Request;

interface BlogPostManager
{
    public function search(Request $request);

    public function publicList();

    public function getLatestPosts($number);

    public function bySlug(string $slug);

    public function getPost(BlogPost $post, Request $request);

    public function store(StoreBlogPostRequest $request);

    public function update(BlogPost $post, UpdateBlogPostRequest $request);

    public function delete(BlogPost $post);

    public function recentPosts(Request $request, int $number = 5);
}
