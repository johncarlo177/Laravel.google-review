<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 * @property int stars
 * @property string name
 * @property string email
 * @property string mobile
 * @property string feedback
 * @property int qrcode_id
 */
class BusinessReviewFeedback extends Model
{
    use HasFactory;

    /**
     * Get the QR code that owns the feedback.
     */
    public function qrcode()
    {
        return $this->belongsTo(QRCode::class, 'qrcode_id');
    }
}
