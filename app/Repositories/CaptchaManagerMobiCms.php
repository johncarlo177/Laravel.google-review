<?php

namespace App\Repositories;

use Mobicms\Captcha\Image;

use Mobicms\Captcha\Code;

use App\Interfaces\CaptchaManager as CaptchaManagerInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CaptchaManagerMobiCms implements CaptchaManagerInterface
{
    private Image $image;

    private string $code;

    private string $sessionKey;

    private int $minLength, $maxLength;

    public function init(?int $minLength = 4, ?int $maxLength = 5)
    {
        $this->minLength = $minLength;

        $this->maxLength = $maxLength;

        $this->code = (string) new Code(lengthMin: $this->minLength, lengthMax: $this->maxLength);

        $this->image = new Image($this->code);

        $this->sessionKey = $this->generateSessionKey();

        $this->saveCache($this->sessionKey, $this->code);
    }

    public function getInlineImage(): string
    {
        return $this->image->__toString();
    }

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    public function validate(string $code, string $sessionKey): bool
    {
        $captchaCode = $this->loadCache($sessionKey);

        $result = strtolower($captchaCode) === strtolower($code);

        if ($result) {
            $this->deleteCache($sessionKey);
        }

        return $result;
    }

    private function generateSessionKey()
    {
        $requestCount = $this->loadCache('request-count') ?: 0;

        $this->saveCache('request-count', ++$requestCount, now()->addCenturies(1));

        $key = $_SERVER['REMOTE_ADDR'] . $_SERVER['REQUEST_TIME'] . $_SERVER['REMOTE_PORT'] . $requestCount;

        return Hash::make($key);
    }

    private function deleteCache($key)
    {
        Cache::forget('captcha-' . $key);
    }

    private function saveCache($key, $value, $expiration = null)
    {
        if (!$expiration) {
            $expiration = now()->addMinutes(10);
        }

        Cache::put('captcha-' . $key, $value, $expiration);
    }

    private function loadCache($key)
    {
        return Cache::get('captcha-' . $key);
    }
}
