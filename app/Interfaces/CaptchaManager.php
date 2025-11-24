<?php

namespace App\Interfaces;

interface CaptchaManager
{
    public function init(?int $minLength = 4, ?int $maxLength = 5);
    public function getSessionKey(): string;
    public function getInlineImage(): string;
    public function validate(string $code, string $sessionKey): bool;
}
