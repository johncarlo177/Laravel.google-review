<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string name
 * @property string display_name
 * @property string locale
 * @property string direction
 * @property int flag_file_id
 * @property bool is_main
 * @property bool is_default
 * @property bool is_active
 */
class Translation extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'locale', 'display_name', 'direction', 'flag_file_id'];

    protected $appends = ['translation_file_id'];

    protected $casts = [
        'is_default' => 'boolean',
        'is_main' => 'boolean'
    ];

    protected $with = ['file'];

    public function translationFileId(): Attribute
    {
        return new Attribute(fn () => $this->file?->id);
    }

    public function file()
    {
        return $this->morphOne(File::class, 'attachable')->orderBy('id', 'desc');
    }


    public function getFlag(): ?File
    {
        return File::find($this->flag_file_id);
    }
}
