<?php

namespace App\Http\Controllers;

use App\Models\TemplateCategory;
use App\Repositories\ModelSearchBuilder;
use App\Support\DatabaseHelper;
use App\Support\System\Traits\WriteLogs;
use Throwable;

class TemplateCategoryController extends Controller
{
    use WriteLogs;

    public function index()
    {
        $search = new ModelSearchBuilder;

        return $search
            ->init(TemplateCategory::class, request())
            ->inColumn('name')
            ->withQuery(function ($query) {
                $query->orderBy('sort_order');
            })
            ->search()
            ->withTransformer(fn($c) => $c->toResponse())
            ->paginate();
    }

    public function store()
    {
        $category = new TemplateCategory();

        DatabaseHelper::forceFillModel($category, request()->all());

        try {
            $category->save();

            return $category;
        } catch (Throwable $th) {
            $this->logWarning($th->getMessage());
        }
    }

    public function show(TemplateCategory $templateCategory)
    {
        return $templateCategory;
    }

    public function update(TemplateCategory $templateCategory)
    {
        DatabaseHelper::forceFillModel($templateCategory, request()->all());

        try {

            $templateCategory->save();

            return $templateCategory;
        } catch (Throwable $th) {
            $this->logWarning($th->getMessage());
        }
    }

    public function destroy(TemplateCategory $templateCategory)
    {
        $templateCategory->delete();

        return $templateCategory;
    }
}
