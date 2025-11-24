<?php

namespace App\Interfaces;

use Closure;

interface MachineTranslation
{
    public function translate(string $query, string $from, string $to);

    public function translateLanguage(string $jsonData, string $to, Closure $saver);
}
