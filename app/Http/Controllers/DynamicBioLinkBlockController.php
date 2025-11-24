<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDynamicBioLinkBlockRequest;
use App\Http\Requests\UpdateDynamicBioLinkBlockRequest;
use App\Interfaces\ModelSearchBuilder;
use App\Models\DynamicBioLinkBlock;
use App\Support\DynamicBioLinkBlocksManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DynamicBioLinkBlockController extends Controller
{
    private DynamicBioLinkBlocksManager $blocks;

    public function __construct()
    {
        $this->blocks = new DynamicBioLinkBlocksManager();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ModelSearchBuilder $search, Request $request)
    {
        if ($request->boolean('list_all')) {
            return DynamicBioLinkBlock::get()->map(function ($block) {
                return $this->transformBlock($block);
            });
        }

        return $search
            ->init(DynamicBioLinkBlock::class, request())
            ->inColumn('name')
            ->search()
            ->paginate();
    }

    protected function transformBlock(DynamicBioLinkBlock $block)
    {
        return array_merge(
            $block->toArray(),
            [
                'icon_url' => file_url($block->icon_id)
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreDynamicBioLinkBlockRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDynamicBioLinkBlockRequest $request)
    {
        return $this->blocks->save($request->all());
    }

    public function storeFile(Request $request)
    {
        return $this->blocks->storeFile($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DynamicBioLinkBlock  $dynamicBioLinkBlock
     * @return \Illuminate\Http\Response
     */
    public function show(DynamicBioLinkBlock $dynamicBioLinkBlock)
    {
        return $dynamicBioLinkBlock;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDynamicBioLinkBlockRequest  $request
     * @param  \App\Models\DynamicBioLinkBlock  $dynamicBioLinkBlock
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDynamicBioLinkBlockRequest $request, DynamicBioLinkBlock $dynamicBioLinkBlock)
    {
        return $this->blocks->save($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DynamicBioLinkBlock  $dynamicBioLinkBlock
     * @return \Illuminate\Http\Response
     */
    public function destroy(DynamicBioLinkBlock $dynamicBioLinkBlock)
    {
        return $this->blocks->delete($dynamicBioLinkBlock);
    }
}
