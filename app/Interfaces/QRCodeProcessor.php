<?php

namespace App\Interfaces;

use Closure;
use App\Support\QRCodeOutput;

interface QRCodeProcessor
{
    public function handle(QRCodeOutput $output, Closure $next);
}
