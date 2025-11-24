<?php

namespace App\Support\Dashboard\MultiQRCodes;

use App\Models\QRCode;
use App\Models\QRCodeScan;
use App\Models\User;
use App\Support\System\Traits\WriteLogs;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class Report
{
    use WriteLogs;

    protected $fromDate = null;

    protected $toDate = null;

    protected $folderIds = [];

    protected User $user;

    public function withUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    public function inFolders($ids)
    {
        $this->folderIds = $ids;

        return $this;
    }

    public function withRequestUser()
    {
        return $this->withUser(request()->user());
    }

    public static function withTopQRCodes()
    {
        return new static;
    }

    public function from($from)
    {
        $this->fromDate = $from ? new Carbon($from) : now()->subMonths(7)->setDay(1);

        return $this;
    }

    public function to($to)
    {
        $this->toDate = $to ? new Carbon($to) : now();

        return $this;
    }

    /**
     * @return Collection<QRCode>
     */
    protected function getQRCodes()
    {
        $query = QRCode::query();

        $query->where('archived', false);

        if ($this->folderIds) {
            $query->whereIn('folder_id', $this->folderIds);
        }

        if (!$this->user->isSuperAdmin()) {
            $query->where('user_id', $this->user->id);
        }

        $query->orderBy('scans_count', 'desc');

        if (empty($this->folderIds)) {
            $query->take(5);
        }

        return $query->get();
    }

    protected function getScansOfQRCodes($ids)
    {
        $query = QRCodeScan::whereIn('qrcode_id', $ids)
            ->where('created_at', '>=', $this->fromDate)
            ->where('created_at', '<=', $this->toDate);

        return $query->get();
    }

    public function build()
    {
        $qrcodes = $this->getQRCodes();

        $scans = $this->getScansOfQRCodes($qrcodes->pluck('id'));

        return $qrcodes->map(
            function (QRCode $qrcode) use ($scans) {
                return Item::withQRCode($qrcode)
                    ->withScans($scans)
                    ->from($this->fromDate)
                    ->to($this->toDate)
                    ->build()
                    ->toArray();
            }
        );
    }
}
