<?php

namespace App\Support\Dashboard\SuperAdminDashboard;

use Illuminate\Contracts\Support\Responsable;

class Model implements Responsable
{
    public $from;
    public $to;
    public $totalProfit;
    public $assessment;
    public $percentageIncrease;
    public $totalUsers;
    public $totalScans;
    public $totalQRCodes;
    public $totalTransactions;
    /**
     * @var TopQRCode[]
     */
    public $topQRCodes;
    /**
     * @var MostScannedQRCode
     */
    public $mostScannedQRCode;
    /**
     * @var MostActiveUser
     */
    public $mostActiveUser;

    public function toResponse($request)
    {
        return array_merge(
            (array)$this,
            [
                'from' => $this->from->format('d F Y'),
                'to' => $this->to->format('d F Y'),
            ]
        );
    }
}
