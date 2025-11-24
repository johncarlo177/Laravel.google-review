<?php

namespace App\Support\AI;

use App\Models\QRCode;

class WorkflowTester
{
    public function run($qrcodeId)
    {
        $qrcode = QRCode::find($qrcodeId);

        $ai = new AIQRCodeGenerator();

        $ai->queue(
            qrcode: $qrcode,
            prompt: 'tasty burger on a wooden table, double size',
            negativePrompt: '',
            qrStrength: 0.65,
            qrSteps: 18
        );
    }
}
