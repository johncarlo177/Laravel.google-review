<?php

namespace App\Models;

use App\Models\Traits\HasMetaFields;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property int id
 * @property array data
 * @property File file
 * @property int file_id
 * @property string type
 * @property string status
 * @property string name
 * @property int user_id
 * @property Collection<BulkOperationResult> results
 * @property User user
 */
class BulkOperationInstance extends Model
{
    use WriteLogs;

    use HasFactory;

    use HasMetaFields;

    public const STATUS_NEW = 'new';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function file()
    {
        return $this->morphOne(File::class, 'attachable');
    }

    public function results()
    {
        return $this->hasMany(BulkOperationResult::class);
    }

    public function getQRCodes()
    {
        return $qrcodeIds = $this->results()->pluck('qrcode_id');
    }

    public function getPrintSignature()
    {
        return sha1(
            $this->user_id . config('app.key') . config('app.name') . $this->id
        );
    }

    public function checkPrintSignature($signature)
    {
        return $signature === $this->getPrintSignature();
    }
}
