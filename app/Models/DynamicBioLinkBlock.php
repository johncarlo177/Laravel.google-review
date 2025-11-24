<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string name
 * @property array fields
 * @property int icon_id
 * @property string custom_code
 */
class DynamicBioLinkBlock extends Model
{
    use HasFactory;

    protected $table = 'dynamic_biolink_blocks';

    protected $fillable = ['name', 'icon_id', 'fields', 'custom_code'];

    protected $casts = [
        'fields' => 'array'
    ];
}
