<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property int feedback_entry_id
 * @property string action
 * @property array|null details
 * @property int|null user_id
 * @property string|null ip_address
 * @property \Carbon\Carbon created_at
 * @property FeedbackEntry feedbackEntry
 * @property User|null user
 */
class FeedbackAuditLog extends Model
{
    use HasFactory;

    protected $table = 'feedback_audit_logs';

    protected $fillable = [
        'feedback_entry_id',
        'action',
        'details',
        'user_id',
        'ip_address',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the feedback entry that owns this audit log
     */
    public function feedbackEntry()
    {
        return $this->belongsTo(FeedbackEntry::class, 'feedback_entry_id');
    }

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

