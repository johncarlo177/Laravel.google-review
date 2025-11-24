<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

/**
 * @property int translation_id
 * @property Translation translation
 */
class ContentBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'position',
        'sort_order',
        'title_alignment',
        'content_alignment',
        'title_color',
        'content_color',
        'background_color',
        'margin',
        'padding',
        'translation_id',
    ];

    public function translation()
    {
        return $this->belongsTo(Translation::class);
    }

    private function markdown($string)
    {
        try {
            return Str::markdown($string);
        } catch (Throwable $th) {
            return null;
        }
    }

    public function contentHtml(): Attribute
    {
        return new Attribute(function () {
            return $this->markdown($this->content);
        });
    }
}
