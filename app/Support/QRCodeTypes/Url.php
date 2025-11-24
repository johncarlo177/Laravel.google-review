<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;
use App\Models\QRCodeRedirect;
use App\Rules\UrlRule;
use App\Support\QRCodeTypes\Interfaces\ShouldImmediatlyRedirectToDestination;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Str;


class Url extends BaseDynamicType implements ShouldImmediatlyRedirectToDestination
{
    use WriteLogs;

    public static function name(): string
    {
        return t('Dynamic URL');
    }

    public static function slug(): string
    {
        return 'url';
    }

    public function rules(): array
    {
        return [
            'url' => ['required', new UrlRule()]
        ];
    }

    public function makeData(QRCode $qrcode): string
    {
        $redirect = QRCodeRedirect::where('qrcode_id', $qrcode->id)->first();

        if (!$redirect) {
            return $this->url($qrcode->data->url);
        }

        return $this->url($redirect->route);
    }

    public function generateName(QRCode $qrcode): string
    {
        return Str::limit($qrcode->data->url, 50);
    }

    public function makeDestination(QRCode $qrcode): string
    {
        return $this->url(@$qrcode->data->url);
    }

    protected function url($string)
    {
        $u = UrlRule::forValue($string)->parse();

        return $u;
    }
}
