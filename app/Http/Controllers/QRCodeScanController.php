<?php

namespace App\Http\Controllers;

use App\Models\QRCodeScan;
use App\Support\QRCodeScanManager;
use Illuminate\Http\Request;

class QRCodeScanController extends Controller
{
    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function count(Request $request, QRCodeScanManager $scans)
    {
        $count = $scans->countScansOfUserQRCodes($request->user());

        return compact('count');
    }

    public function collectLanguage(Request $request, QRCodeScan $scan)
    {
        $signature = $request->input('signature');

        $valid = (new QRCodeScanManager)->isLanguageCollectionSignatureValid(
            $scan,
            $signature
        );

        if (!$valid) {
            abort(401);
        }

        $scan->language = $request->input('language');

        $scan->save();

        return '';
    }
}
