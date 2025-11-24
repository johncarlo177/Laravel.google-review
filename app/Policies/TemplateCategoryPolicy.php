<?php

namespace App\Policies;


use App\Models\TemplateCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TemplateCategoryPolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function list(User $user)
    {
        return $user->permitted('template-category.list-all');
    }

    public function show(
        User $user,
        TemplateCategory $templateCategory
    ) {
        return true;
    }

    public function store(User $actor)
    {
        return $actor->permitted('template-category.store');
    }

    public function update(User $actor, TemplateCategory $templateCategory)
    {
        return $actor->permitted('template-category.update-any');
    }

    public function destroy(User $actor, TemplateCategory $templateCategory)
    {
        return $actor->permitted('template-categeory.destroy-any');
    }
}
