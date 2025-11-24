<?php

namespace App\Support\QRCodeTypes;

use App\Models\QRCode;
use App\Support\QRCodeTypes\BusinessReview\FeedbackEmail;
use App\Support\QRCodeTypes\BusinessReview\FeedbackSaver;
use App\Support\QRCodeTypes\BusinessReview\RedirectManager;
use App\Support\System\Traits\WriteLogs;

class BusinessReview extends BaseDynamicType
{
    use WriteLogs;

    public static function name(): string
    {
        return t('Business Review');
    }

    public static function slug(): string
    {
        return 'business-review';
    }

    public function rules(): array
    {
        return [
            'businessName' => 'required',
        ];
    }

    public function generateName(QRCode $qrcode): string
    {
        return $qrcode->data->businessName;
    }

    public function renderView(QRCode $qrcode)
    {
        if (request()->method() === 'POST') {
            return $this->handlePostRequest($qrcode);
        }

        return parent::renderView($qrcode);
    }

    protected function handlePostRequest(QRCode $qrcode)
    {
        $feedback = request()->input('feedback');

        $stars = request()->input('stars');

        FeedbackSaver::withData(
            array_merge(
                request()->all(),
                [
                    'qrcode_id' => $qrcode->id
                ]
            )
        )->save();

        FeedbackEmail::withQRCode($qrcode)
            ->withFeedback($feedback)
            ->withStars($stars)
            ->send();

        return RedirectManager::withQRCode($qrcode)
            ->withStars($stars)
            ->redirect();
    }
}
