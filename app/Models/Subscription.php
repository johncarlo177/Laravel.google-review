<?php

namespace App\Models;

use App\Interfaces\SubscriptionManager;
use App\Models\Traits\HasMetaFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int billing_details_custom_form_response_id
 * @property CustomFormResponse billing_details
 * @property User user
 * @property SubscriptionPlan subscription_plan
 * @property Collection statuses
 * @property Carbon expires_at
 * @property Carbon updated_at
 */
class Subscription extends Model
{
    use HasFactory, HasMetaFields;

    protected $fillable = [
        'subscription_plan_id',
        'user_id',
        'expires_at',
        'billing_details_custom_form_response_id'
    ];

    protected $casts = [
        'expires_at' => 'datetime:Y-m-d'
    ];

    protected static function booted()
    {
        static::saved(
            function (Subscription $subscription) {

                if ($subscription->wasChanged('expires_at')) {
                    /**
                     * @var SubscriptionManager
                     */
                    $subscriptions = app(SubscriptionManager::class);

                    $subscriptions->setExpiredSubscriptions([
                        $subscription
                    ]);
                }
            }
        );
    }

    public function statuses()
    {
        return $this
            ->hasMany(
                SubscriptionStatus::class
            )->orderBy('id', 'desc');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription_plan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getExpiresAtDate()
    {
        return new Carbon($this->expires_at);
    }

    public function billing_details()
    {
        return $this->belongsTo(CustomFormResponse::class, 'billing_details_custom_form_response_id');
    }
}
