<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BlogPostPolicy
{
    use HandlesAuthorization;

    public function list(User $user)
    {
        return $user->permitted('blog-post.list-all');
    }

    public function show(User $user, BlogPost $blogPost)
    {
        return $user->permitted('blog-post.show-any');
    }

    public function store(User $user)
    {
        return $user->permitted('blog-post.store');
    }

    public function update(User $user, BlogPost $blogPost)
    {
        return $user->permitted('blog-post.update-any');
    }

    public function destroy(User $user, BlogPost $blogPost)
    {
        return $user->permitted('blog-post.destroy-any');
    }
}
