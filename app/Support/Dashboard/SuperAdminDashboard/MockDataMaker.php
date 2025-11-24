<?php

namespace App\Support\Dashboard\SuperAdminDashboard;

class MockDataMaker
{
    public function make()
    {
        $model = new Model();

        $model->from = now()->subMonths(3);

        $model->to = now();

        $model->assessment = 'You have a great performance 💪';

        $model->totalProfit = 3874;

        $model->percentageIncrease = 78 . '%';

        $model->totalUsers = 320;

        $model->totalScans = 156870;

        $model->totalQRCodes = 8301;

        $model->totalTransactions = 89;

        $model->mostScannedQRCode = [
            'qrcode_id' => 1,
            'scans' => 13028,
        ];

        $model->mostActiveUser = [
            'user_id' => 1,
            'user_name' => '',
            'total_qrcodes' => 130,
        ];

        $model->topQRCodes = [
            [
                'typeSlug' => 'url',
                'count' => 3541,
            ],
            [
                'typeSlug' => 'google-review',
                'count' => 2300,
            ],
            [
                'typeSlug' => 'biolinks',
                'count' => 2153,
            ],
            [
                'typeSlug' => 'restaurant-menu',
                'count' => 1986,
            ],
            [
                'typeSlug' => 'product-catalogue',
                'count' => 852,
            ],
        ];

        return $model;
    }
}
