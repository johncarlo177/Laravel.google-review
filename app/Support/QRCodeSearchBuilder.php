<?php

namespace App\Support;

use App\Models\QRCode;
use App\Models\User;
use App\Policies\QRCodePolicy;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class QRCodeSearchBuilder
{
    use WriteLogs;

    protected Builder $query;

    protected User $user;

    protected FolderManager $folders;

    protected $paginatePath;

    private $pageSize = 10;

    private $sort = null;

    protected $shouldPaginate = true;

    public function __construct()
    {
        $this->query = QRCode::query();

        $this->query->with('user');

        $this->query->where('is_template', false);

        $this->folders = new FolderManager();
    }

    public function with($relations)
    {
        $this->query->with($relations);

        return $this;
    }

    public function paginationPath($path)
    {
        $this->paginatePath = $path;

        return $this;
    }

    public function withPageSize($pageSize = 10)
    {
        if (!$pageSize) {
            $pageSize = 10;
        }

        $this->pageSize = $pageSize;

        if (isDemo()) {
            $this->pageSize = min($pageSize, 100);
        }

        return $this;
    }

    public function withoutPagination($noPagination = false)
    {
        $this->shouldPaginate = !$noPagination;

        return $this;
    }

    /**
     * @return LengthAwarePaginator|Collection<QRCode>
     */
    public function paginate()
    {
        if (!$this->shouldPaginate) {
            $this->logDebug('No pagination');

            return $this->query->get();
        }

        /**
         * @var LengthAwarePaginator
         */
        $paginated = $this->query->paginate(
            perPage: $this->pageSize,
            page: request()->input('page')
        );

        $paginated->withPath($this->paginatePath);

        return $paginated;
    }

    public function withSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    private function getSortColumn()
    {
        if (!is_string($this->sort)) {
            return null;;
        }

        $key = @explode(',', $this->sort)[0];

        if (!DatabaseHelper::hasColumn('qrcodes', $key)) {
            return null;
        }

        return $key;
    }

    private function getSortDir()
    {
        $dir = @explode(',', $this->sort)[1];

        if ($dir !== 'asc' && $dir !== 'desc') {
            $dir = 'asc';
        }

        return $dir;
    }

    public function sort()
    {
        $columnn = $this->getSortColumn();

        if (!$columnn) {
            $this->query->orderBy('id', 'desc');
        } else {
            $dir = $this->getSortDir();

            $this->query->getQuery()->orderBy($columnn, $dir);
        }

        return $this;
    }

    public function applySubUserRestrictions()
    {
        if (!$this->user->is_sub) return $this;

        $folderIds = $this->folders
            ->getSubuserFolders(
                $this->user
            )->pluck('id');

        $this->query->whereIn('folder_id', $folderIds);

        return $this;
    }

    public function applyClientRestrictions()
    {
        if (QRCodePolicy::canListAll($this->user)) return $this;

        if ($this->user->is_sub) return $this;

        $this->query->where(function ($query) {
            $query->where('user_id', $this->user->id);

            $ids = $this->getSubIds();

            if (empty($ids)) return;

            $query->orWhereIn('user_id', $ids);
        });

        return $this;
    }

    protected function getSubIds()
    {
        $resellerUserIds = User::where(
            'reseller_id',
            $this->user->id
        )
            ->pluck('id')
            ->all();

        $subIds = $this->user->sub_users->pluck('id')->all();

        return array_merge(
            $resellerUserIds,
            $subIds
        );
    }



    public function type($type)
    {
        if (empty($type) || $type == '*') return $this;

        $type = explode(',', $type);

        if (!is_array($type)) {
            $type = [$type];
        }

        $this->query->whereIn('type', $type);

        return $this;
    }

    public function name($name)
    {
        if (empty($name)) return $this;

        $this->query->where(
            DB::raw('lower(name)'),
            'like',
            '%' . strtolower($name) . '%'
        );

        return $this;
    }

    public function withKeyword($keyword)
    {
        if (empty($keyword)) return $this;

        $this->query->where(function ($query) use ($keyword) {

            $query->whereHas('redirect', function ($query) use ($keyword) {
                $query->where('slug', $keyword);
            })
                ->orWhere(
                    DB::raw('lower(`name`)'),
                    'like',
                    '%' . strtolower($keyword) . '%'
                )
                ->orWhere('name', $keyword)
                ->orWhere('id', $keyword)
                ->orWhere(
                    DB::raw('lower(`tags`)'),
                    'like',
                    '%' . strtolower($keyword) . '%'
                )->orWhere('tags', $keyword);
        });

        return $this;
    }

    public function folder($folderId)
    {
        if (empty($folderId) || !is_numeric($folderId)) return $this;

        $this->query->where('folder_id', $folderId);

        return $this;
    }

    public function archived($archived)
    {
        $this->query->whereArchived($archived);

        return $this;
    }

    public function byUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function forQrCodesCreatedBy($user_id)
    {
        if (empty($user_id)) return $this;

        $this->query->where('user_id', $user_id);

        return $this;
    }

    public function scansRange($range)
    {
        $range = json_decode($range, true);

        if (!$range) {
            return $this;
        }

        if (!empty(@$range['from'])) {
            $this->query->where('scans_count', '>=', $range['from']);
        }

        if (!empty(@$range['to'])) {
            $this->query->where('scans_count', '<=', $range['to']);
        }

        $this->logDebug('Range = %s', json_encode($range, JSON_PRETTY_PRINT));

        return $this;
    }
}
