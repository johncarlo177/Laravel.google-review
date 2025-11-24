<?php

namespace App\Interfaces;

use Illuminate\Http\Request;

use App\Models\QRCode;

interface QRCodeGenerator
{
    public function init(QRCode $model, string $outputType);

    public function initFromRequest(Request $request);

    public function respondInline();

    public function writeString();

    public function saveVariants(QRCode $model);

    public static function processor($processor);
}
