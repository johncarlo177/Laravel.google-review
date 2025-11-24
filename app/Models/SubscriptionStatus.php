<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon created_at
 * @property string status
 */
class SubscriptionStatus extends Model
{
    use HasFactory;

    public $timestamps = false;

    const STATUS_PENDING_PAYMENT = 'pending_payment';

    const STATUS_ACTIVE = 'active';

    const STATUS_EXPIRED = 'expired';

    const STATUS_CANCELED = 'canceled';

    protected $fillable = ['subscription_id', 'status'];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public static function getStatuses()
    {
        $ref = new \ReflectionClass(static::class);
        $constants = $ref->getConstants();

        $statuses = array_filter($constants, function ($val, $key) {
            return preg_match('/STATUS_/', $key);
        }, ARRAY_FILTER_USE_BOTH);


        return array_values($statuses);
    }
}
