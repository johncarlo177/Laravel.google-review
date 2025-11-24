<?php

namespace App\Support\Google;

use App\Models\Config;
use App\Models\QRCode;
use App\Plugins\PluginManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GooglePlace
{
    public const TYPE_REVIEW_COLLECTION = 'review-collection';
    public const TYPE_REVIEW_LIST = 'review-list';
    public const TYPE_BUSINESS_PAGE = 'my-business';

    use WriteLogs;

    protected $data = null;
    protected $type = null;

    /**
     * @param object|array $data qrcg-google-place-input value
     */
    public static function withData($data)
    {
        $instance = new static;

        $instance->data = (array)$data;

        return $instance;
    }

    public function attachTo(QRCode $qrcode)
    {
        if (empty($qrcode->getMeta('google_place_details'))) {
            // 
            $qrcode->setMeta(
                'google_place_details',
                $this->getPlaceDetails()
            );
        }

        return $this;
    }

    public function withType($type)
    {
        $this->type = $type;

        return $this;
    }

    protected function getPlaceId()
    {
        return @$this->data['place_id'];
    }

    protected function shouldUseGoogleMapsAppReviewCollection()
    {
        $config = config('google-review.review-collection-destination');

        return $config === 'app';
    }


    public function makeWebReviewRequestUrl()
    {
        $url = 'https://search.google.com/local/writereview?placeid=' . $this->getPlaceId();

        return $url;
    }

    public function makeGoogleMapsAppReview()
    {
        return 'https://www.google.com/maps/search/?api=1&query=Review&query_place_id=' . $this->getPlaceId();
    }

    /**
     * Generate either web review request url or app review request 
     * based on the system settings.
     */
    public function makeReviewCollectionUrl()
    {
        if ($this->shouldUseGoogleMapsAppReviewCollection()) {
            return $this->makeGoogleMapsAppReview();
        }

        return $this->makeWebReviewRequestUrl();
    }

    public function getPlaceDetails()
    {
        return Cache::remember(
            __METHOD__ . $this->getPlaceId(),
            now()->addMonth(),
            function () {

                $placeId = $this->getPlaceId();

                $baseURl = 'https://maps.googleapis.com/maps/api/place/details/json';

                $baseURl .= sprintf('?place_id=%s&key=%s', $placeId, $this->getApiKey());

                $data = Http::get($baseURl)->json();

                if (!isset($data['result'])) {
                    $this->logError('Invalid Google Places response. ' . json_encode($data, JSON_PRETTY_PRINT));
                }

                $place = @$data['result'];

                return $place;
            }
        );
    }

    /**
     * @return PlaceDetails
     */
    public function getPlaceDetailsModel()
    {
        return PlaceDetails::withData($this->getPlaceDetails());
    }

    public static function getApiKey()
    {
        return config('services.google.api_key');
    }

    public function makeReviewListUrl()
    {
        return 'https://search.google.com/local/reviews?placeid=' . $this->getPlaceId();
    }

    public function makeBusinessPageUrl()
    {
        return @$this->getPlaceDetails()['url'];
    }

    public function getPlaceName()
    {
        return @$this->getPlaceDetails()['name'];
    }

    protected function getType()
    {
        $type = $this->type;

        if (empty($type)) {
            $type = Config::get(
                'google-review.review-collection-destination'
            );
        }

        $type = PluginManager::doFilter(
            PluginManager::FILTER_GOOGLE_PLACE_URL_TYPE,
            $type
        );

        return $type;
    }

    public function makeDestinationUrl()
    {
        $type = $this->getType();

        switch ($type) {
            case GooglePlace::TYPE_BUSINESS_PAGE:
                return $this->makeBusinessPageUrl();
            case GooglePlace::TYPE_REVIEW_LIST:
                return $this->makeReviewListUrl();
            case GooglePlace::TYPE_REVIEW_COLLECTION:
                return $this->makeReviewCollectionUrl();
            default:
                return $this->makeReviewCollectionUrl();
        }
    }
}
