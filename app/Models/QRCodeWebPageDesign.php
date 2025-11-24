<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * @property int qrcode_id
 * @property QRCode qrcode
 * @property array design
 */
class QRCodeWebPageDesign extends Model
{
    protected $table = 'qrcode_webpage_designs';

    protected $casts = [
        'design' => 'array'
    ];

    protected $fillable = ['design'];

    public function qrcode()
    {
        return $this->belongsTo(QRCode::class);
    }

    public function value($key, $default = null)
    {
        try {
            $value = $this->design[$key];

            if (empty($value)) {
                return $default;
            }

            return $value;
        } catch (Throwable $th) {
            return $default;
        }
    }

    public function setValue($key, $value)
    {
        $design = $this->design ?? [];

        $design[$key] = $value;

        $this->design = $design;

        $this->save();
    }
}
