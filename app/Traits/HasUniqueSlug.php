<?php

namespace App\Traits;

use Illuminate\Support\Str;


trait HasUniqueSlug
{
    protected $slugLength = 10;

    public static function bootHasUniqueSlug()
    {
        static::creating([static::class, 'onCreating']);
    }

    public static function onCreating($model)
    {
        $model->generateUniqueSlug();
    }

    private function generateUniqueSlug()
    {
        $slug = $this->randomSlug();

        while (static::whereSlug($slug)->first()) {
            $slug = $this->randomSlug();
        }

        $this->slug = $slug;
    }

    private function randomSlug()
    {
        return strtolower(Str::random($this->slugLength));
    }
}
