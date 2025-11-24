<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 * @property string name
 * @property string email
 * @property string subject
 * @property string message
 * @property string notes
 */
class Contact extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'message', 'subject', 'notes'];
}
