<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string name
 * @property int user_id
 * @property array<QRCode> qrcodes
 * @property User user
 */
class Folder extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function qrcodes()
    {
        return $this->hasMany(QRCode::class);
    }
}
