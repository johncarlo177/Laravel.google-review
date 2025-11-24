<?php

namespace App\Support\QRCodeTypes\Interfaces;

use Illuminate\Http\Request;

interface HandlesApiCalls
{
    public function apiEntryPoint(Request $request);
}
