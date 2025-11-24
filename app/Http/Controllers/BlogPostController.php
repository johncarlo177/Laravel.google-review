<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBlogPostRequest;
use App\Http\Requests\UpdateBlogPostRequest;
use App\Interfaces\BlogPostManager;
use App\Interfaces\FileManager;
use App\Models\BlogPost;
use Illuminate\Http\Request;


class BlogPostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(BlogPostManager $posts, Request $request)
    {
        return $posts->search($request);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBlogPostRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBlogPostRequest $request, BlogPostManager $posts)
    {
        return $posts->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BlogPost  $blogPost
     * @return \Illuminate\Http\Response
     */
    public function show(BlogPost $blogPost, Request $request, BlogPostManager $posts)
    {
        return $posts->getPost($blogPost, $request);
    }

    public function uploadFeaturedImage(Request $request, BlogPost $post, FileManager $files)
    {
        $request->merge([
            'attachable_type' => $post::class,
            'attachable_id' => $post->id,
            'type' => FileManager::FILE_TYPE_BLOG_POST_FEATURED_IMAGE
        ]);

        return $files->store($request);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBlogPostRequest  $request
     * @param  \App\Models\BlogPost  $blogPost
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost, BlogPostManager $posts)
    {
        return $posts->update($blogPost, $request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BlogPost  $blogPost
     * @return \Illuminate\Http\Response
     */
    public function destroy(BlogPost $blogPost, BlogPostManager $posts)
    {
        return $posts->delete($blogPost);
    }
}
