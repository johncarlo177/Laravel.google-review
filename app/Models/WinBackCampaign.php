<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int id
 * @property int user_id
 * @property string name
 * @property string|null description
 * @property int|null segment_id
 * @property string|null segment_name
 * @property string status
 * @property \Carbon\Carbon|null scheduled_at
 * @property \Carbon\Carbon|null started_at
 * @property \Carbon\Carbon|null completed_at
 * @property int total_customers
 * @property int messages_sent
 * @property int responses_received
 * @property int customers_returned
 * @property float revenue_recovered
 * @property array|null automation_settings
 * @property bool is_automated
 * @property \Carbon\Carbon created_at
 * @property \Carbon\Carbon updated_at
 * @property User user
 * @property WinBackSegment|null segment
 * @property \Illuminate\Database\Eloquent\Collection messages
 * @property \Illuminate\Database\Eloquent\Collection responses
 */
class WinBackCampaign extends Model
{
    use HasFactory;

    protected $table = 'win_back_campaigns';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'segment_id',
        'segment_name',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_customers',
        'messages_sent',
        'responses_received',
        'customers_returned',
        'revenue_recovered',
        'automation_settings',
        'is_automated',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_customers' => 'integer',
        'messages_sent' => 'integer',
        'responses_received' => 'integer',
        'customers_returned' => 'integer',
        'revenue_recovered' => 'decimal:2',
        'automation_settings' => 'array',
        'is_automated' => 'boolean',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAUSED = 'paused';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get the user that owns this campaign
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the segment for this campaign
     */
    public function segment(): BelongsTo
    {
        return $this->belongsTo(WinBackSegment::class, 'segment_id');
    }

    /**
     * Get all messages in this campaign
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WinBackMessage::class, 'campaign_id');
    }

    /**
     * Get all responses for this campaign
     */
    public function responses(): HasMany
    {
        return $this->hasMany(WinBackResponse::class, 'campaign_id');
    }

    /**
     * Calculate response rate
     */
    public function getResponseRate(): float
    {
        if ($this->messages_sent === 0) {
            return 0;
        }
        return ($this->responses_received / $this->messages_sent) * 100;
    }

    /**
     * Calculate return rate
     */
    public function getReturnRate(): float
    {
        if ($this->messages_sent === 0) {
            return 0;
        }
        return ($this->customers_returned / $this->messages_sent) * 100;
    }
}
