<?php

namespace App\Http\Controllers;

use App\Support\Dashboard\MultiQRCodes\Report;
use App\Support\Dashboard\SuperAdminDashboard\Builder as SuperAdminDashboard;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use WriteLogs;

    public function getSuperUserDashboard()
    {
        if (!$this->optionalUser()?->isSuperAdmin()) {
            return null;
        }

        return (new SuperAdminDashboard)->build()->getModel();
    }

    public function getMultiQRCodesReport(Request $request)
    {
        if ($request->input('folder_ids')) {

            $folderIds = explode(',', $request->input('folder_ids'));
            // 
        } else {

            $folderIds = [];
            // 
        }

        return Report::withTopQRCodes()
            ->withRequestUser()
            ->from($request->input('from'))
            ->to($request->input('to'))
            ->inFolders($folderIds)
            ->build();
    }
}
