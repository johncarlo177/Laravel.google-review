<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string email
 * @property string otp
 * @property Carbon verified_at
 */
class OtpVerification extends Model
{
    protected $table = 'otp_verifications';

    protected $casts = [
        'verified_at' => 'datetime'
    ];
}
