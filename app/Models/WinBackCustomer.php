<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property int user_id
 * @property string|null name
 * @property string|null email
 * @property string|null phone
 * @property \Carbon\Carbon|null last_visit_date
 * @property float total_spend
 * @property int visit_count
 * @property float lifetime_value
 * @property string|null customer_type
 * @property int|null days_since_last_visit
 * @property string|null segment
 * @property array|null metadata
 * @property bool is_active
 * @property \Carbon\Carbon|null last_contacted_at
 * @property \Carbon\Carbon created_at
 * @property \Carbon\Carbon updated_at
 * @property User user
 * @property \Illuminate\Database\Eloquent\Collection messages
 * @property \Illuminate\Database\Eloquent\Collection responses
 */
class WinBackCustomer extends Model
{
    use HasFactory;

    protected $table = 'win_back_customers';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'last_visit_date',
        'total_spend',
        'visit_count',
        'lifetime_value',
        'customer_type',
        'days_since_last_visit',
        'segment',
        'metadata',
        'is_active',
        'last_contacted_at',
    ];

    protected $casts = [
        'last_visit_date' => 'date',
        'total_spend' => 'decimal:2',
        'visit_count' => 'integer',
        'lifetime_value' => 'decimal:2',
        'days_since_last_visit' => 'integer',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'last_contacted_at' => 'datetime',
    ];

    const SEGMENT_LOST = 'lost';
    const SEGMENT_DORMANT = 'dormant';
    const SEGMENT_VIP = 'vip';
    const SEGMENT_ONE_TIME = 'one-time';
    const SEGMENT_FAILED_LEAD = 'failed-lead';

    /**
     * Get the user (business owner) that owns this customer
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all messages sent to this customer
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WinBackMessage::class, 'customer_id');
    }

    /**
     * Get all responses from this customer
     */
    public function responses(): HasMany
    {
        return $this->hasMany(WinBackResponse::class, 'customer_id');
    }

    /**
     * Check if customer is lost (60+ days)
     */
    public function isLost(): bool
    {
        return $this->days_since_last_visit >= 60;
    }

    /**
     * Check if customer is dormant (30-59 days)
     */
    public function isDormant(): bool
    {
        return $this->days_since_last_visit >= 30 && $this->days_since_last_visit < 60;
    }

    /**
     * Check if customer is VIP (high lifetime value)
     */
    public function isVip(): bool
    {
        return $this->lifetime_value >= 500 || $this->visit_count >= 10;
    }

    /**
     * Get preferred contact method
     */
    public function getPreferredChannel(): string
    {
        if ($this->phone) {
            return 'sms';
        }
        if ($this->email) {
            return 'email';
        }
        return 'email'; // Default
    }
}
