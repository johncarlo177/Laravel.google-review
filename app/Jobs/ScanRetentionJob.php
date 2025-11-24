<?php

namespace App\Jobs;

use App\Models\QRCode;
use App\Models\QRCodeScan;
use App\Models\User;
use App\Repositories\UserManager;
use Illuminate\Support\Facades\DB;

class ScanRetentionJob
{

    public function handle()
    {
        $users = User::get();

        $users->each(function (User $user) {
            $this->deleteUnNeededScans($user);
        });
    }

    protected function deleteUnNeededScans(User $user)
    {
        if ($user->isSuperAdmin()) {
            return;
        }

        $userManager = new UserManager();

        $plan = $userManager->getCurrentPlan($user);

        if (!$plan) {
            return;
        }

        $days = $plan->scan_retention_days;

        if ($days == -1) {
            return;
        }

        $qrcodeIds = QRCode::where('user_id', $user->id)->pluck('id');

        $scanIds = QRCodeScan::whereIn(
            'qrcode_id',
            $qrcodeIds
        )->where(
            'created_at',
            '<=',
            now()->subDays($days)
        )
            ->pluck('id');

        DB::table('qrcode_scans')->whereIn('id', $scanIds)->delete();
    }
}
