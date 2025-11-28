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
 * @property string display_name
 * @property int|null days_threshold
 * @property string|null default_message_template
 * @property array|null message_tone
 * @property string|null preferred_channel
 * @property bool is_active
 * @property \Carbon\Carbon created_at
 * @property \Carbon\Carbon updated_at
 * @property User user
 * @property \Illuminate\Database\Eloquent\Collection campaigns
 */
class WinBackSegment extends Model
{
    use HasFactory;

    protected $table = 'win_back_segments';

    protected $fillable = [
        'user_id',
        'name',
        'display_name',
        'days_threshold',
        'default_message_template',
        'message_tone',
        'preferred_channel',
        'is_active',
    ];

    protected $casts = [
        'days_threshold' => 'integer',
        'message_tone' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns this segment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get campaigns using this segment
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(WinBackCampaign::class, 'segment_id');
    }
}
