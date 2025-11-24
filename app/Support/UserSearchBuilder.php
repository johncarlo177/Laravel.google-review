<?php

namespace App\Support;

use App\Models\Transaction;
use App\Models\User;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class UserSearchBuilder
{
    use WriteLogs;

    /**
     * @var Builder
     */
    protected $query;

    protected int $pageSize = 10;

    protected array $searchableColumns = [];

    protected bool $orderByIdOnPaginate = true;

    protected $transformer = null;

    protected $keyword = '';

    protected User $actor;

    protected $paying = null;

    protected $number_of_qrcodes = null;

    public function __construct()
    {

        $this->query = User::query();

        $this->searchableColumns = ['name', 'email', 'id'];
    }

    public static function withActor(User $actor)
    {
        $instance = new static;

        $instance->actor = $actor;

        return $instance;
    }

    public function withPaying($paying)
    {
        $this->paying = $paying;

        return $this;
    }

    public function withNumberOfQRCodes($range)
    {
        if (!$range) {
            return $this;
        }

        $range = json_decode($range, true);

        if (!is_array($range)) {
            return $this;
        }

        $this->number_of_qrcodes = $range;


        return $this;
    }

    protected function applyNumberOfQRCodesSearch()
    {
        if (!is_array($this->number_of_qrcodes)) {
            return $this;
        }

        $range = $this->number_of_qrcodes;

        $min = @$range['from'];

        $max = @$range['to'];

        if ($min) {
            $this->query->having(
                'qrcodes_count',
                '>=',
                $min
            );
        }

        if ($max) {
            $this->query->having(
                'qrcodes_count',
                '<=',
                $max
            );
        }

        return $this;
    }

    public function canAccess(User $subject)
    {
        $this->applyActorRestrictions();

        return $this->query->where('id', $subject->id)->count() > 0;
    }

    protected function applyActorRestrictions()
    {
        if ($this->actor->isReseller()) {
            $this->query->where('reseller_id', $this->actor->id);
        }
    }

    protected function applyPayingSearch()
    {
        if ($this->paying === null) {
            $this->logDebug('paying = null');
            return $this;
        }

        if ($this->paying) {

            $this->logDebug('paying = true');

            $this->logDebug('Adding paying restrictions');

            $this->query->whereHas('transactions', function ($query) {
                $query->where('status', Transaction::STATUS_SUCCESS);
            });
        } else {
            $this->logDebug('paying = false');

            $this->query->whereDoesntHave('transactions', function ($query) {
                $query->where('status', Transaction::STATUS_SUCCESS);
            });
        }
    }

    public function build()
    {
        $this->withRelations();
        $this->applyActorRestrictions();
        $this->applyKeywoardSearch();
        $this->applyPayingSearch();
        $this->applyNumberOfQRCodesSearch();

        return $this;
    }

    protected function withRelations()
    {
        $this->query->with('transactions');
        $this->query->with('roles');
        $this->query->with('parent_user');
        $this->query->withCount('qrcodes');
    }

    public function withKeyword($keyword)
    {
        $this->keyword = $keyword;

        return $this;
    }

    protected function applyKeywoardSearch()
    {
        if (empty($this->keyword)) {
            return $this;
        }

        $this->query->where(
            /**
             * @var Builder
             */
            function ($query) {

                foreach ($this->searchableColumns as $i => $column) {

                    $key = DB::raw("lower($column)");

                    $value = '%' . strtolower($this->keyword) . '%';

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

        if (!empty(request()->path)) {
            $paginated->withPath(request()->path);
        }

        $paginated->setCollection(
            $this->applyTransformer($paginated->getCollection())
        );

        return $paginated;
    }
}
