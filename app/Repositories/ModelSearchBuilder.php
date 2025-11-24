<?php

namespace App\Repositories;

use App\Interfaces\ModelSearchBuilder as ModelSearchBuilderInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class ModelSearchBuilder implements ModelSearchBuilderInterface
{
    protected Request $request;

    protected Builder $query;

    protected int $pageSize = 10;

    protected array $searchableColumns = [];

    protected bool $orderByIdOnPaginate = true;

    protected $transformer = null;

    public function init(
        $class,
        Request $request,
        $orderByIdOnPaginate = true,
        $query = null
    ): self {

        $this->request = $request;

        if ($query) {

            $this->query = $query;
            //
        } else {

            $this->query = $class::query();
        }

        $this->orderByIdOnPaginate = $orderByIdOnPaginate;

        return $this;
    }

    public function withTransformer($transformer)
    {
        $this->transformer = $transformer;

        return $this;
    }

    public function withQuery(callable $cp): self
    {
        $cp($this->query);

        return $this;
    }

    public function inColumn(string $column): self
    {
        return $this->inColumns([$column]);
    }

    public function inColumns($searchableColumns =  ['name']): self
    {
        $this->searchableColumns = array_merge($this->searchableColumns, $searchableColumns);

        return $this;
    }

    /**
     * @return LengthAwarePaginator|Collection
     */
    public function paginate(?callable $transformer = null)
    {
        if ($transformer) {
            $this->transformer = $transformer;
        }

        if ($this->orderByIdOnPaginate) {
            $this->query->orderBy('id', 'desc');
        }

        if (request()->boolean('no-pagination')) {
            return $this->applyTransformer($this->query->get());
        }


        /**
         * @var LengthAwarePaginator
         */
        $paginated = $this->query->paginate(10);

        if (!empty($this->request->path)) {
            $paginated->withPath($this->request->path);
        }

        $paginated->setCollection(
            $this->applyTransformer($paginated->getCollection())
        );

        return $paginated;
    }

    /**
     * @param Collection $collection
     */
    protected function applyTransformer($collection)
    {
        if (!is_callable($this->transformer)) {
            return $collection;
        }

        return $collection->transform($this->transformer);
    }

    public function search(): self
    {
        if (empty($this->request->keyword)) {
            return $this;
        }

        $this->query->where(
            /**
             * @var Builder
             */
            function ($query) {

                foreach ($this->searchableColumns as $i => $column) {

                    $key = DB::raw("lower($column)");

                    $value = '%' . strtolower($this->request->keyword) . '%';

                    if (preg_match('/\./', $column)) {
                        $parts = explode('.', $column);
                        $relation = implode('.', array_slice($parts, 0, count($parts) - 1));
                        $field = $parts[count($parts) - 1];
                        $key = DB::raw("lower($field)");

                        if ($i === 0)
                            $query->whereHas($relation, function ($query) use ($key, $value) {
                                $query->where($key, 'like', $value);
                            });
                        else
                            $query->orWhereHas($relation, function ($query) use ($key, $value) {
                                $query->where($key, 'like', $value);
                            });
                    } else {

                        if ($i === 0)
                            $query->where($key, 'like', $value);
                        else
                            $query->orWhere($key, 'like', $value);
                    }
                }
            }
        );



        return $this;
    }

    public function withPageSize($pageSize): static
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    public function query(): Builder
    {
        return $this->query;
    }
}
