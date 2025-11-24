<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string name
 * @property string currency_code
 * @property string symbol
 * @property string symbol_position
 * @property string thousands_separator
 * @property string decimal_separator
 * @property string decimal_separator_enabled
 */
class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'currency_code',
        'symbol',
        'symbol_position',
        'thousands_separator',
        'decimal_separator',
        'decimal_separator_enabled'
    ];
}
