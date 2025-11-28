<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int id
 * @property int message_id
 * @property int customer_id
 * @property int campaign_id
 * @property string type
 * @property string|null content
 * @property string|null channel
 * @property bool is_positive
 * @property bool notified_business
 * @property \Carbon\Carbon|null notified_at
 * @property array|null metadata
 * @property \Carbon\Carbon created_at
 * @property \Carbon\Carbon updated_at
 * @property WinBackMessage message
 * @property WinBackCustomer customer
 * @property WinBackCampaign campaign
 */
class WinBackResponse extends Model
{
    use HasFactory;

    protected $table = 'win_back_responses';

    protected $fillable = [
        'message_id',
        'customer_id',
        'campaign_id',
        'type',
        'content',
        'channel',
        'is_positive',
        'notified_business',
        'notified_at',
        'metadata',
    ];

    protected $casts = [
        'is_positive' => 'boolean',
        'notified_business' => 'boolean',
        'notified_at' => 'datetime',
        'metadata' => 'array',
    ];

    const TYPE_REPLY = 'reply';
    const TYPE_CLICK = 'click';
    const TYPE_VISIT = 'visit';
    const TYPE_BOOKING = 'booking';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_UNSUBSCRIBE = 'unsubscribe';
    const TYPE_IGNORE = 'ignore';

    /**
     * Get the message this response is for
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(WinBackMessage::class, 'message_id');
    }

    /**
     * Get the customer who responded
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(WinBackCustomer::class, 'customer_id');
    }

    /**
     * Get the campaign this response belongs to
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WinBackCampaign::class, 'campaign_id');
    }

    /**
     * Mark business as notified
     */
    public function markNotified(): void
    {
        $this->update([
            'notified_business' => true,
            'notified_at' => now(),
        ]);
    }
}
