<?php namespace App\Repositories;

use App\Interfaces\QRCodeStats as Contract;

use App\Models\QRCode;

use App\Models\QRCodeScan;

use Illuminate\Support\Facades\DB;

class QRCodeStats implements Contract {
    private QRCode $model;

    function __construct(QRCode $model) {
        $this->model = $model;
    }

    function getStats(QRCode $qrcode) {

        $this->model = $qrcode;

        $result = QRCodeScan::where('qrcode_id', $this->model->id)
            ->groupBy('date')
            ->orderBy('date', 'DESC')
            ->get(array(
                DB::raw('Date(created_at) as date'),
                DB::raw('COUNT(*) as "scans"')
            ));

        return $result;
    }
}