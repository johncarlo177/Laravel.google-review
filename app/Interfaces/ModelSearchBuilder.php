<?php

namespace App\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

interface ModelSearchBuilder
{
    public function init(
        $class,
        Request $request,
        $orderByIdOnPaginate = true,
        $query = null
    ): self;

    public function withQuery(callable $cp): self;

    public function inColumn(string $column): self;

    public function inColumns(array $searchableColumns = ['name']): self;

    public function search(): self;

    public function query(): Builder;

    public function withPageSize($pageSize): static;

    public function withTransformer($transformer);

    /**
     * @return LengthAwarePaginator|Collection
     */
    public function paginate(?callable $transformer = null);
}
