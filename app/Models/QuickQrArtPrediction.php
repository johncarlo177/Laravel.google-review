<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string api_id
 * @property string status
 * @property QuickQrArtAInput input
 * @property int user_id
 * @property int qrcode_id
 * @property string output
 * @property User user
 * @property QRCode qrcode;
 * @property int output_file_id
 */
class QuickQrArtPrediction extends Model
{
    const STATUS_QUEUED = 'queued';
    const STATUS_EXECUTED = 'executed';

    use HasFactory;

    public static function fromApiResponse(
        $response,
        QRCode $qrcode,
    ) {
        if (empty($response)) return null;

        $instance = new static;

        $instance->api_id = $response['id'];

        $instance->user_id = $qrcode->user_id;

        $instance->qrcode_id = $qrcode->id;

        $instance->status = $response['status'];

        $instance->input = QuickQrArtInput::fromArray($response['input']);

        $instance->save();

        return $instance;
    }

    public static function ofQRCode(QRCode $qrcode): ?static
    {
        return static::query()
            ->where('qrcode_id', $qrcode->id)
            ->orderBy('id', 'desc')
            ->first();
    }

    public function isSuccess()
    {
        return $this->status === $this::STATUS_EXECUTED;
    }

    public function input(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => QuickQrArtInput::fromArray(json_decode($value, true)),
            set: fn ($value) => json_encode($value)
        );
    }

    public function qrcode()
    {
        return $this->belongsTo(QRCode::class, 'qrcode_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getOutputFile(): ?File
    {
        return File::find($this->output_file_id);
    }
}
