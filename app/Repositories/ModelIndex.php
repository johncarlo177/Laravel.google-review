<?php

namespace App\Repositories;

use App\Interfaces\ModelIndex as ModelIndexInterface;

use Illuminate\Http\Request;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use \Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\Log;

class ModelIndex implements ModelIndexInterface
{
    private Model $model;

    private Request $request;

    private Builder $query;

    public function build(Model $model, Request $request)
    {
        $this->model = $model;

        $this->request = $request;

        $this->query = $model->query();

        return $this;
    }

    public function list($searchableColumns = ['name'])
    {
        if (!empty($this->request->keyword)) {

            foreach ($searchableColumns as $i => $column) {

                $key = DB::raw("lower($column)");

                $value = '%' . strtolower($this->request->keyword) . '%';

                if (preg_match('/\./', $column)) {
                    $parts = explode('.', $column);
                    $relation = implode('.', array_slice($parts, 0, count($parts) - 1));
                    $field = $parts[count($parts) - 1];
                    $key = DB::raw("lower($field)");

                    if ($i === 0)
                        $this->query->whereHas($relation, function ($query) use ($key, $value) {
                            $query->where($key, 'like', $value);
                        });
                    else
                        $this->query->orWhereHas($relation, function ($query) use ($key, $value) {
                            $query->where($key, 'like', $value);
                        });
                } else {

                    if ($i === 0)
                        $this->query->where($key, 'like', $value);
                    else
                        $this->query->orWhere($key, 'like', $value);
                }
            }
        }

        $this->query->orderBy('id', 'desc');

        $paginated = $this->query->paginate(10);

        $paginated->withPath($this->request->path);

        return $paginated;
    }

    public function withQuery(callable $cp)
    {
        $cp($this->query);
        return $this;
    }
}
