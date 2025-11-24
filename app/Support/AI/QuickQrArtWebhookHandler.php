<?php

namespace App\Support\AI;

use App\Models\QRCode;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;

class QuickQrArtWebhookHandler
{
    use WriteLogs;

    private AIQRCodeGenerator $ai;

    public function __construct()
    {
        $this->ai = new AIQRCodeGenerator();
    }

    public function handle(Request $request)
    {
        $this->logDebugf(
            'Receiving quickqrart webhook %s',
            json_encode($request->all(), JSON_PRETTY_PRINT)
        );

        $data = $request->all();

        $api_id = @$data['id'];

        $output = @$data['output'];

        if (empty($api_id) || empty($output)) {
            return $this->logWarningf('Empty id or output');
        }

        $this->ai->savePredictionResponse(@$data['id'], @$data['output'], @$data['status']);
    }
}
