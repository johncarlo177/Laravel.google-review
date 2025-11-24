<?php

namespace App\Support\QRCodeTypes\ViewComposers\Cache;

use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Cache;

class ViewCacheManager
{
    use WriteLogs;

    protected $path = null;

    protected $composer = null;

    public static function withPath($path)
    {
        $instance = new static;

        $instance->path = $path;

        return $instance;
    }

    public function withComposer($composer)
    {
        $this->composer = $composer;

        return $this;
    }

    protected function key()
    {
        $qrcodeSlug = request()->path();

        return __CLASS__ . '::path(' . $this->path . ')[' . $qrcodeSlug . ']';
    }

    public function clear()
    {
        return Cache::forget($this->key());
    }

    protected function build()
    {
        return view($this->path, [
            'composer' => $this->composer,
        ])->render();
    }

    protected function renderCache()
    {
        $content = Cache::get($this->key());

        $this->logDebug('Content length is (%s)', strlen($content));

        if (!$content) {

            Cache::put($this->key(), $this->build(), 3600 * 24 * 100);
            // 
            $content = Cache::get($this->key());
            // 
        }

        $this->logDebug('Content length is (%s)', strlen($content));

        return $content;
    }

    protected function shouldRenderCache()
    {
        if (request()->boolean('preview')) {
            return false;
        }

        return true;
    }

    public function render()
    {
        if ($this->shouldRenderCache()) {
            return $this->renderCache();
        }

        // Recache after each render.
        dispatch(function () {
            $this->clear();
            $this->renderCache();
        })->afterResponse();

        return $this->build();
    }
}
