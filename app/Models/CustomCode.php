<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string name
 * @property string language
 * @property string position
 * @property string code
 * @property int sort_order
 */
class CustomCode extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'language', 'sort_order', 'position', 'code'];
}
