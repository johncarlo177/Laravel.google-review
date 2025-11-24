<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property string uuid
 * @property string name
 * @property int qrcode_id
 * @property string widget_background_color
 * @property string widget_text_color
 * @property int widget_icon_id
 * @property string widget_hover_background_color
 * @property string widget_hover_text_color
 * @property string widget_position
 * @property int user_id
 * @property QRCode qrcode
 * @property User user
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Widget extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function (Widget $widget) {
            $widget->uuid = Str::uuid();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function qrcode()
    {
        return $this->belongsTo(QRCode::class, 'qrcode_id');
    }
}
