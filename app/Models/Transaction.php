<?php

namespace App\Models;

use App\Models\Traits\HasMetaFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property User user
 * @property string description
 * @property string status
 * @property Subscription subscription
 * @property string source
 * @property float amount
 * @property int user_id
 */
class Transaction extends Model
{
    use HasFactory, HasMetaFields;

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    const STATUS_PENDING_CONFIRMATION = 'pending_confirmation';

    public $casts = [
        'approved_at' => 'date',
        'rejected_at' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payment_proof()
    {
        return $this->morphOne(File::class, 'attachable');
    }
}
