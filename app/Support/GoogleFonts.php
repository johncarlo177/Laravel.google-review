<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Google font variants: 5148
 * Google font count: 1442
 */
class GoogleFonts
{
    private $cacheFs;

    const BASE_URL = 'http://fonts.gstatic.com';

    public function __construct()
    {
        $this->cacheFs = Storage::disk('db');
    }

    private function cachePath()
    {
        return 'raw/google_fonts.json';
    }

    public function list()
    {
        $cached = $this->cacheFs->get($this->cachePath());

        $cached = json_decode($cached, true);

        if (empty($cached)) {
            $response = $this->get();

            $cached = $response->body();

            $cached = json_decode($cached, true);

            $filtered = $this->removeLinksFromAllFontItems($cached);

            $this->cacheFs->put($this->cachePath(), json_encode($filtered, JSON_PRETTY_PRINT));
        }

        return $this->addLinksToAllFontItems($cached['items']);
    }

    private function removeLinksFromAllFontItems($googleFonts)
    {
        $fonts = $googleFonts['items'];

        $fonts = array_map(function ($item) {

            $files = array_reduce(array_keys($item['files']), function ($result, $fontKey) use ($item) {
                $result[$fontKey] = str_replace(static::BASE_URL, 'BASE_URL', $item['files'][$fontKey]);
                return $result;
            }, []);

            return array_merge([], $item, compact('files'));
        }, $fonts);

        $googleFonts['items'] = $fonts;

        return $googleFonts;
    }

    private function addLinksToAllFontItems($items)
    {
        return array_map(function ($item) {

            $files = array_reduce(array_keys($item['files']), function ($result, $fontKey) use ($item) {
                $result[$fontKey] = str_replace('BASE_URL', static::BASE_URL, $item['files'][$fontKey]);
                return $result;
            }, []);

            return array_merge([], $item, compact('files'));
        }, $items);
    }

    public function clearFontCache()
    {
        foreach (glob(Storage::path('google_fonts/*.ttf')) as $file) {
            Storage::delete($file);
        }
    }

    public function countVariants()
    {
        $cached = $this->cacheFs->get($this->cachePath());

        $fonts = json_decode($cached, true)['items'];

        return array_reduce($fonts, function ($sum, $font) {
            return $sum + count($font['variants']);
        }, 0);
    }

    public function listFamilies()
    {
        $list = $this->list();

        return array_map(function ($font) {
            $item = [];

            $item['family'] = $font['family'];

            $item['variants'] = $font['variants'];

            return $item;
        }, $list);
    }

    public function details($family)
    {
        return collect($this->list())->first(function ($font) use ($family) {
            return $font['family'] === $family;
        });
    }

    public function getFontFile($family, $variant = 'regular')
    {
        $path = sprintf("google_fonts/%s_%s.ttf", $family, $variant);

        if (Storage::exists($path)) {
            return Storage::path($path);
        }

        $font = $this->details($family);

        $files = $font['files'];

        if (!$variant) {
            // Get thickest variation
            $variant = collect($font['variants'])->reduce(function ($max, $v) {
                if (is_numeric($v)) {
                    $max = max($max, $v);
                }

                return $max;
            }, 0) ?: 'regular';
        }

        $variant_is_found = array_filter($font['variants'], fn ($v) => $v === $variant);

        if (!$variant_is_found) {
            $fallback = 'regular';
            $variant = $fallback;
        }

        $data = file_get_contents($files[$variant]);

        Storage::put($path, $data);

        return Storage::path($path);
    }

    private function get()
    {
        $key = config('services.google.api_key');

        $key = !empty(json_decode($key)) ? json_decode($key) : $key;

        return Http::get(
            'https://www.googleapis.com/webfonts/v1/webfonts?key=' . $key
        );
    }
}
