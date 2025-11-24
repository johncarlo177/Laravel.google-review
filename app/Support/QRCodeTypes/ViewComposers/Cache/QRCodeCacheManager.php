<?php

namespace App\Support\QRCodeTypes\ViewComposers\Cache;

use App\Models\QRCode;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

/**
 * @deprecated 
 * @see App\Support\QRCodeTypes\ViewComposers\Cache\ViewCacheManager
 */
class QRCodeCacheManager
{
    use WriteLogs;

    protected QRCode $qrcode;

    public static function withQRCode(QRCode $qrcode)
    {
        $instance = new static;

        $instance->qrcode = $qrcode;

        return $instance;
    }

    protected function key()
    {
        return __CLASS__ . '::qrcode-id(' . $this->qrcode->id . ')';
    }

    public function clear()
    {
        return Cache::forget($this->key());
    }

    protected function build()
    {
        return view('qrcode.types.' . $this->qrcode->type)->render();
    }

    protected function renderCache()
    {
        $content = Cache::rememberForever(
            $this->key(),
            $this->build(...)
        );

        return $this->processCsrfToken($content);
    }

    protected function processCsrfToken($content)
    {
        $newToken = csrf_token();

        $this->logDebug('Processing csrf token %s', $newToken);

        $content = preg_replace(
            '/name="_token" value=".*?"/',
            sprintf('name="_token" value="%s"', $newToken),
            $content
        );

        $content = preg_replace(
            '/name="csrf-token" content=".*?"/',
            sprintf('name="csrf-token" content="%s"', $newToken),
            $content
        );

        return $content;
    }

    protected function shouldRenderCache()
    {
        if (isLocal()) {
            return true;
        }

        if (request()->boolean('preview')) {
            return false;
        }

        return $this->qrcode->resolveType()->shouldCacheView();
    }

    public function renderRealTime()
    {
        return $this->build();
    }

    public function render()
    {
        return $this->build();
    }

    public function renderCacheIfNeeded()
    {
        if ($this->shouldRenderCache()) {
            return $this->renderCache();
        }

        return $this->build();
    }
}
