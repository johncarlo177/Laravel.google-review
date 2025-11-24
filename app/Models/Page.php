<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string title
 * @property string slug
 * @property string html_content
 * @property string meta_description
 * @property string head_tag_code
 * @property boolean published
 * @property \Carbon\Carbon created_at
 * @property \Carbon\Carbon updated_at
 */
class Page extends Model
{
    use HasFactory;

    public $fillable = [
        'title',
        'slug',
        'html_content',
        'published',
        'meta_description',
        'head_tag_code',
    ];

    public $casts = [
        'published' => 'boolean',
    ];

    public function render()
    {
        return view('blue.pages.dynamic', [
            'page' => $this
        ]);
    }
}
