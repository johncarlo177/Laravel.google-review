<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int qrcode_id
 * @property int rating
 * @property string|null comment
 * @property string|null contact
 * @property string|null sentiment
 * @property string|null urgency
 * @property string|null category
 * @property bool escalate
 * @property string|null gpt_reply
 * @property string|null gpt_next_step
 * @property string|null gpt_suggested_remedy
 * @property array|null gpt_actions
 * @property string status
 * @property \Carbon\Carbon created_at
 * @property \Carbon\Carbon updated_at
 * @property QRCode qrcode
 */
class FeedbackEntry extends Model
{
    use HasFactory;

    protected $table = 'feedback_entries';

    protected $fillable = [
        'qrcode_id',
        'rating',
        'comment',
        'contact',
        'sentiment',
        'urgency',
        'category',
        'escalate',
        'gpt_reply',
        'gpt_next_step',
        'gpt_suggested_remedy',
        'gpt_actions',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'escalate' => 'boolean',
        'gpt_actions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUS_NEW = 'new';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_ESCALATED = 'escalated';

    const SENTIMENT_POSITIVE = 'positive';
    const SENTIMENT_NEUTRAL = 'neutral';
    const SENTIMENT_NEGATIVE = 'negative';

    const URGENCY_LOW = 'low';
    const URGENCY_MEDIUM = 'medium';
    const URGENCY_HIGH = 'high';

    /**
     * Get the QR code that owns the feedback.
     */
    public function qrcode()
    {
        return $this->belongsTo(QRCode::class, 'qrcode_id');
    }

    /**
     * Get audit logs for this feedback entry
     */
    public function auditLogs()
    {
        return $this->hasMany(FeedbackAuditLog::class, 'feedback_entry_id');
    }

    /**
     * Check if feedback is positive (4-5 stars)
     */
    public function isPositive()
    {
        return $this->rating >= 4;
    }

    /**
     * Check if feedback needs escalation
     */
    public function needsEscalation()
    {
        return $this->escalate || $this->urgency === self::URGENCY_HIGH;
    }
}

