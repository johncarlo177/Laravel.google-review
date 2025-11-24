<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomCodeRequest;
use App\Http\Requests\UpdateCustomCodeRequest;
use App\Interfaces\ModelSearchBuilder;
use App\Models\CustomCode;
use Illuminate\Http\Request;

class CustomCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ModelSearchBuilder $search, Request $request)
    {
        return $search
            ->init(CustomCode::class, $request)
            ->inColumns([
                'name',
                'language',
                'position'
            ])
            ->search()
            ->paginate();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCustomCodeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCustomCodeRequest $request)
    {
        $customCode = new CustomCode($request->all());

        $customCode->save();

        return $customCode;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CustomCode  $customCode
     * @return \Illuminate\Http\Response
     */
    public function show(CustomCode $customCode)
    {
        return $customCode;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCustomCodeRequest  $request
     * @param  \App\Models\CustomCode  $customCode
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCustomCodeRequest $request, CustomCode $customCode)
    {
        $customCode->fill($request->all());

        $customCode->save();

        return $customCode;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomCode  $customCode
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomCode $customCode)
    {
        $customCode->delete();

        return $customCode;
    }

    public function getPositions()
    {
        return json_decode(config('content-manager.custom-code-positions')) ?? [];
    }
}
