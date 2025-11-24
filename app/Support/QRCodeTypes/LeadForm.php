<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;
use App\Support\System\Traits\WriteLogs;
use Carbon\Carbon;
use Illuminate\Validation\Validator;

class LeadForm extends BaseDynamicType
{
    use WriteLogs;

    public static function slug(): string
    {
        return 'lead-form';
    }

    public static function name(): string
    {
        return t('Lead Form');
    }

    public function rules(): array
    {
        return [
            'form_name' => 'required',
        ];
    }

    public function generateName(QRCode $qrcode): string
    {
        return t('Lead Form: ') . $qrcode->data->form_name;
    }
}
