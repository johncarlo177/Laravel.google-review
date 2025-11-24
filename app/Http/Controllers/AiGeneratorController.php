<?php

namespace App\Http\Controllers;

use App\Models\QRCode;
use Illuminate\Http\Request;
use App\Models\QuickQrArtPrediction;
use App\Support\AI\AIQRCodeGenerator;
use App\Exceptions\MonthlyLimitReached;
use App\Http\Middleware\ErrorMessageMiddleware;

class AiGeneratorController extends Controller
{
    private AIQRCodeGenerator $ai;

    public function __construct()
    {
        $this->ai = new AIQRCodeGenerator;
    }

    public function generate(Request $request, QRCode $qrcode)
    {
        try {
            //
            $prediction = $this->ai->queue(
                qrcode: $qrcode,
                prompt: $request->ai_prompt,
                negativePrompt: '',
                qrStrength: $request->ai_strength,
                qrSteps: $request->ai_steps,
                shortModelVersion: $request->ai_model
            );

            return $prediction;
            //
        } catch (MonthlyLimitReached $ex) {
            //
            ErrorMessageMiddleware::abortWithMessage(
                $ex->getMessage()
            );
            //
        }
    }

    public function fetchPrediction(Request $request, QRCode $qrcode)
    {
        return QuickQrArtPrediction::ofQRCode($qrcode);
    }
}
