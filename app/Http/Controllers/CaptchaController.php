<?php

namespace App\Http\Controllers;

use App\Interfaces\CaptchaManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class CaptchaController extends Controller
{
    private CaptchaManager $captcha;

    public function __construct(CaptchaManager $captcha)
    {
        $this->captcha = $captcha;
    }

    public function getCaptcha()
    {
        $this->captcha->init();

        return [
            'session_key' => $this->captcha->getSessionKey(),
            'image' => $this->captcha->getInlineImage()
        ];
    }
}
