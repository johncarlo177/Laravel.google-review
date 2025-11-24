<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property array configs
 * @property array fields
 * @property User user
 * @property string related_model_id
 * @property string related_model
 * @property int user_id
 */
class LeadForm extends Model
{
    use HasFactory;

    protected $casts = [
        'configs' => 'array',
        'fields' => 'array'
    ];

    public function isMultipleSubmissionAllowed()
    {
        return @$this->configs['multiple_submission'] != 'disabled';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolveQRCode()
    {
        return $this->getQRCode() ?? $this->getWebpageDesign()?->qrcode;
    }

    private function getQRCode(): ?QRCode
    {
        if ($this->related_model != class_basename(QRCode::class)) {
            return null;
        }

        return QRCode::find($this->related_model_id);
    }

    private function getWebpageDesign(): ?QRCodeWebPageDesign
    {
        if ($this->related_model != class_basename(QRCodeWebPageDesign::class)) {
            return null;
        }

        return QRCodeWebPageDesign::find($this->related_model_id);
    }

    public function responses()
    {
        return $this->hasMany(LeadFormResponse::class);
    }
}
