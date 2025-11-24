<?php

namespace App\Models;

use App\Interfaces\FileManager;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

/**
 * @property File featured_image
 * @property string featured_image_src
 * @property int translation_id
 * @property Translation translation
 */

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'excerpt',
        'published_at',
        'meta_description',
        'translation_id',
        'featured_image_id',
    ];

    protected $casts = [
        'published_at' => 'date:Y-m-d'
    ];

    public static function booted()
    {
        static::creating(function ($model) {
            $model->onCreating();
        });
    }

    public function onCreating()
    {
        $this->slug = Str::slug($this->title);

        $i = 0;

        while (static::where('slug', $this->slug)->first()) {
            $this->slug = $this->slug . '-' . (++$i);
        }
    }

    public function url(): Attribute
    {
        return new Attribute(get: fn() => route('post', ['post' => $this->slug]));
    }

    public function html(): Attribute
    {
        return new Attribute(fn() => Str::markdown($this->content));
    }

    public function excerpt(): Attribute
    {
        return new Attribute(
            fn($value) =>
            empty($value) ? Str::words($this->content, 25) : Str::words($value, 25)
        );
    }

    public function translation()
    {
        return $this->belongsTo(Translation::class);
    }

    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', Carbon::today());
    }


    public function featuredImageSrc(): Attribute
    {
        return new Attribute(function () {
            return file_url($this->featured_image_id);
        });
    }
}
