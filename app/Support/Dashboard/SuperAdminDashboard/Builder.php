<?php

namespace App\Support\Dashboard\SuperAdminDashboard;

use App\Models\QRCode;
use App\Models\Transaction;
use App\Models\User;
use App\Support\QRCodeScanManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class Builder
{
    protected Model $model;

    public function __construct()
    {
        $this->model = new Model;
    }

    public function build()
    {
        $this
            ->initFrom()
            ->initTo()
            ->initTotalProfit()
            ->initPercentageIncrease()
            ->initAssessment()
            ->initTotalRegisteredUsers()
            ->initTotalScans()
            ->initTotalQRCodes()
            ->initTotalTransactions()
            ->initTopQRCodes()
            ->initMostScannedQRCode()
            ->initMostActiveUser();

        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    protected function initFrom()
    {
        if (isset($this->model->from)) {
            return $this;
        }

        /**
         * @var User
         */
        $firstUser = User::orderBy('id')->first();

        $this->model->from = $firstUser->created_at;

        return $this;
    }

    protected function initTo()
    {
        if (isset($this->model->to)) {
            return $this;
        }

        $this->model->to = now();

        return $this;
    }

    protected function initTotalProfit()
    {
        $this->model->totalProfit = Transaction::whereStatus(
            Transaction::STATUS_SUCCESS
        )->pluck('amount')->reduce(function ($total, $amount) {
            return $total + $amount;
        }, 0);

        return $this;
    }

    protected function initTotalRegisteredUsers()
    {
        $this->model->totalUsers = User::count();

        return $this;
    }

    protected function initTotalScans()
    {
        $scanManager = new QRCodeScanManager;

        $this->model->totalScans = $scanManager->getGlobalScansCount();

        return $this;
    }

    protected function initTotalQRCodes()
    {
        $this->model->totalQRCodes = QRCode::count();

        return $this;
    }

    protected function initTotalTransactions()
    {
        $this->model->totalTransactions = Transaction::count();

        return $this;
    }

    protected function initTopQRCodes()
    {
        $records = DB::table('qrcodes')
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->orderBy('total', 'desc')
            ->get();

        $this->model->topQRCodes = collect($records)
            ->map(
                function ($record) {
                    // 
                    $model = new TopQRCodeType;

                    $model->typeSlug = $record->type;

                    $model->count = $record->total;

                    return $model;
                }
            )
            ->all();

        return $this;
    }

    protected function initMostScannedQRCode()
    {
        /**
         * @var QRCode
         */
        $qrcode = QRCode::orderByDesc('scans_count')->first();

        if (!$qrcode) {
            return $this;
        }

        $model = new MostScannedQRCode;

        $model->qrcode_id = $qrcode?->id;

        $model->scans = $qrcode->scans_count;

        $this->model->mostScannedQRCode = $model;

        return $this;
    }

    protected function initMostActiveUser()
    {
        /**
         * @var Collection<User>
         */
        $users = User::get();

        $user = $users->sort(function (User $u1, User $u2) {
            return $u2->qrcodes()->count() - $u1->qrcodes()->count();
        })->first();

        $record = new MostActiveUser;

        $record->total_qrcodes = $user->qrcodes()->count();

        $record->user_email = $user->email;

        $record->user_id = $user->id;

        $record->user_name = $user->name;

        $this->model->mostActiveUser = $record;

        return $this;
    }

    protected function initAssessment()
    {
        $this->model->assessment = 'You have a great performance 💪';

        return $this;
    }

    protected function initPercentageIncrease()
    {
        $this->model->percentageIncrease = 81.5 . '%';

        return $this;
    }
}
