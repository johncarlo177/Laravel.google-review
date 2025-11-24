<?php

namespace App\Support\QRCodeTypes\BusinessReview;

use App\Models\QRCode;
use App\Support\Google\GooglePlace;
use App\Support\System\Traits\WriteLogs;

class RedirectManager
{
    use WriteLogs;

    private QRCode $qrcode;

    private $stars = null;

    private $numberOfStarsToRedirect = 3;

    public static function withQRCode(QRCode $qrcode)
    {
        $instance = new static;

        $instance->qrcode = $qrcode;

        $instance->numberOfStarsToRedirect = @$qrcode->data->numberOfStarsToRedirect ?? 3;

        return $instance;
    }

    public function withStars($stars)
    {
        $this->stars = $stars;

        return $this;
    }

    public function redirect()
    {
        $url = $this->stars >= $this->numberOfStarsToRedirect ? $this->getFinalReviewUrl() : $this->getQRCodeSuccessPageUrl();

        $this->logDebug('url = %s', $url);

        return redirect()->to($url);
    }

    private function getQRCodeSuccessPageUrl()
    {
        return $this->qrcode->redirect->route . '?success=true';
    }

    public function getFinalReviewUrl()
    {
        if ($this->isCustomReviewUrl()) {
            return $this->getCustomReviewUrl();
        }

        if ($this->isGoogleReview()) {
            return $this->google_place()->makeReviewCollectionUrl();
        }

        return $this->getQRCodeSuccessPageUrl();
    }

    protected function isCustomReviewUrl()
    {
        return $this->field('action') === 'review_url' && !empty($this->getCustomReviewUrl());
    }

    protected function isGoogleReview()
    {
        return $this->field('action') === 'google_review' && !empty($this->getGooglePlaceData());
    }

    protected function getGooglePlaceData()
    {
        return $this->field('google_place');
    }

    protected function getCustomReviewUrl()
    {
        return $this->field('review_url');
    }

    protected function google_place()
    {
        return GooglePlace::withData($this->getGooglePlaceData());
    }

    protected function field($key)
    {
        return @$this->qrcode->data->{$key};
    }
}
