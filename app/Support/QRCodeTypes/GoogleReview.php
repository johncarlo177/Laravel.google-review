<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;
use App\Support\Google\GooglePlace;
use App\Support\QRCodeTypes\Interfaces\HandlesApiCalls;
use App\Support\QRCodeTypes\Interfaces\ShouldImmediatlyRedirectToDestination;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Illuminate\Validation\Validator;
use Throwable;

class GoogleReview extends BaseDynamicType implements
    ShouldImmediatlyRedirectToDestination,
    HandlesApiCalls
{
    use WriteLogs;

    public static function slug(): string
    {
        return 'google-review';
    }

    public static function name(): string
    {
        return t('Google Review');
    }

    public function rules(): array
    {
        return [
            'place' => 'required'
        ];
    }

    public function generateName(QRCode $qrcode): string
    {
        try {
            return $this->google_place($qrcode)->getPlaceName();
        } catch (Throwable $th) {

            $this->logError('Google API Error ' . $th->getMessage());

            $validator = FacadesValidator::make([], []);

            $validator->after(function (Validator $validator) {
                $validator->errors()->add(
                    'place',
                    t('Cannot communicate with Google API, make sure Places API is enabled with your current credentials.')
                );
            });

            $validator->validate();
        }

        return t('Google Review');
    }

    private function getUrlType(QRCode $qrcode)
    {
        return @$qrcode->data->url_type;
    }

    public function makeDestination(QRCode $qrcode): string
    {
        return $this->google_place($qrcode)->makeDestinationUrl();
    }

    public function apiEntryPoint(Request $request)
    {
        if ($request->input('method') === 'getGoogleMapsApiKey') {
            return [
                'api_key' => GooglePlace::getApiKey(),
            ];
        }

        return abort(404, 'Method not found');
    }

    private function google_place(QRCode $qrcode)
    {
        return GooglePlace::withData(@$qrcode->data->place)
            ->withType($this->getUrlType($qrcode));
    }
}
