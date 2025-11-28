<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int id
 * @property int campaign_id
 * @property int customer_id
 * @property string channel
 * @property string message_content
 * @property string|null recipient_email
 * @property string|null recipient_phone
 * @property string status
 * @property string|null external_id
 * @property \Carbon\Carbon|null sent_at
 * @property \Carbon\Carbon|null delivered_at
 * @property string|null error_message
 * @property bool is_ai_generated
 * @property array|null ai_metadata
 * @property \Carbon\Carbon created_at
 * @property \Carbon\Carbon updated_at
 * @property WinBackCampaign campaign
 * @property WinBackCustomer customer
 * @property WinBackResponse|null response
 */
class WinBackMessage extends Model
{
    use HasFactory;

    protected $table = 'win_back_messages';

    protected $fillable = [
        'campaign_id',
        'customer_id',
        'channel',
        'message_content',
        'recipient_email',
        'recipient_phone',
        'status',
        'external_id',
        'sent_at',
        'delivered_at',
        'error_message',
        'is_ai_generated',
        'ai_metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'is_ai_generated' => 'boolean',
        'ai_metadata' => 'array',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_BOUNCED = 'bounced';

    const CHANNEL_SMS = 'sms';
    const CHANNEL_EMAIL = 'email';
    const CHANNEL_WHATSAPP = 'whatsapp';
    const CHANNEL_MESSENGER = 'messenger';
    const CHANNEL_PUSH = 'push';
    const CHANNEL_INBOX = 'inbox';

    /**
     * Get the campaign this message belongs to
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WinBackCampaign::class, 'campaign_id');
    }

    /**
     * Get the customer this message was sent to
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(WinBackCustomer::class, 'customer_id');
    }

    /**
     * Get the response to this message (if any)
     */
    public function response(): HasOne
    {
        return $this->hasOne(WinBackResponse::class, 'message_id');
    }

    /**
     * Mark message as sent
     */
    public function markAsSent(string $externalId = null): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'external_id' => $externalId,
        ]);
    }

    /**
     * Mark message as delivered
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    /**
     * Mark message as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }
}
