<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Interfaces\ModelSearchBuilder;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ModelSearchBuilder $search, Request $request)
    {
        return $search
            ->init(Page::class, $request)
            ->inColumns(['title', 'slug'])
            ->search()
            ->paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePageRequest $request)
    {
        $page = new Page($request->all());

        $page->save();

        return $page;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function show(Page $page)
    {
        return $page;
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePageRequest  $request
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePageRequest $request, Page $page)
    {

        $newSlug = $request->slug;

        $existingPageHasNewSlug = Page::whereSlug($newSlug)->where('id', '<>', $page->id)->first();

        if ($existingPageHasNewSlug) {
            $validator = Validator::make([], [], []);

            $validator->after(function () use ($validator) {
                $validator->errors()->add('slug', t('Slug is already taken.'));
            });

            return $validator->validate();
        }

        $page->fill($request->all());

        $page->save();

        return $page;
    }

    public function viewPage(Request $request)
    {
        $slug = str_replace('/', '', $request->getPathInfo());

        /**
         * @var Page
         */
        $page = Page::whereSlug($slug)->first();

        if (!$page) {
            abort(404);
        }

        return $page->render();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function destroy(Page $page)
    {
        $page->delete();

        return $page;
    }
}
