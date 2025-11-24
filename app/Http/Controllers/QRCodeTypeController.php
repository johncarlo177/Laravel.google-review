<?php

namespace App\Http\Controllers;

use App\Support\QRCodeTypes\Interfaces\HandlesApiCalls;
use App\Support\QRCodeTypes\QRCodeTypeManager;
use Illuminate\Http\Request;

class QRCodeTypeController extends Controller
{
    private QRCodeTypeManager $qrcodeTypeManager;

    public function __construct()
    {
        $this->qrcodeTypeManager = new QRCodeTypeManager;
    }

    public function apiCall($slug, Request $request)
    {
        $type = $this->qrcodeTypeManager->find($slug);

        if (!($type instanceof HandlesApiCalls)) {
            abort(404, t('Not found'));
        }

        return $type->apiEntryPoint($request);
    }
}
