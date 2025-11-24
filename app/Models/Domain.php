<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string host
 * @property string protocol
 * @property int user_id
 * @property User user
 * @property string availability
 * @property boolean is_default
 * @property int sort_order
 * @property boolean readonly
 * @property string status
 * @property int home_page_qrcode_id
 * @property QRCode homePageQRCode
 * @property \Carbon\Carbon created_at
 * @property \Carbon\Carbon updated_at
 */
class Domain extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_IN_PROGRESS = 'in-progress';
    const STATUS_PUBLISHED = 'published';
    const STATUS_REJECTED = 'rejected';


    const AVAILABILITY_PUBLIC = 'public';
    const AVAILABILITY_PRIVATE = 'private';

    protected $fillable = ['host', 'protocol', 'home_page_qrcode_id'];

    protected $casts = [
        'public' => 'boolean',
    ];

    public function homePageQRCode()
    {
        return $this->belongsTo(QRCode::class, 'home_page_qrcode_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
