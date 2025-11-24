<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @property int id
 * @property int custom_form_id
 * @property int user_id
 * @property array fields
 * @property CustomForm custom_form
 * @property User user
 */
class CustomFormResponse extends Model
{
    use HasFactory;

    protected $casts = [
        'fields' => 'array'
    ];

    public function custom_form()
    {
        return $this->belongsTo(CustomForm::class);
    }
}
