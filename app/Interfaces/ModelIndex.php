<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

use Illuminate\Database\Eloquent\Model;

/** 
 * @deprecated
 * 
 * @see \App\Interfaces\ModelSearchBuilder
 */
interface ModelIndex
{
    public function build(Model $model, Request $request);

    public function withQuery(callable $cp);

    public function list(array $searchableColumns = ['name']);
}
