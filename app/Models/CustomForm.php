<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property array fields
 * @property array settings
 * @property string name
 * @property string type
 * @property string related_model
 * @property int related_model_id
 * @property User user
 * @property int user_id
 */
class CustomForm extends Model
{
    use HasFactory;

    public const TYPE_AUTOMATIC_FORM_POPUP = 'qrcode.automatic_form_popup';

    protected $casts = [
        'fields' => 'array',
        'settings' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getSettings($key, $default = null)
    {
        if (!is_array($this->settings)) {
            return $default;
        }

        return @$this->settings[$key] ?? $default;
    }

    /**
     * @return QRCode
     */
    public function getQRCode()
    {
        if ($this->related_model != 'QRCode') {
            return null;
        }

        return QRCode::find($this->related_model_id);
    }
}
