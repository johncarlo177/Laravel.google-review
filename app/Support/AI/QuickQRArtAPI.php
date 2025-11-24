<?php

namespace App\Support\AI;

use App\Models\Config;
use App\Models\QuickQrArtInput;
use Illuminate\Support\Facades\Http;

class QuickQRArtAPI
{
    private $apiKey = '';

    private string $baseUrl = 'https://api.quickqr.art/';

    public function __construct()
    {
        $this->apiKey = Config::get('quickqr_art.api_key');
    }

    public function queue(
        QuickQrArtInput $input
    ) {

        $endpoint = 'v1/predictions/queue';

        $response = $this->request()->post($endpoint, $input->toArray());

        return $response->json();
    }

    public function getPrediction($prediction_id)
    {
        $endpoint = 'v1/predictions/' . $prediction_id;

        return $this->request()->get($endpoint);
    }

    private function request()
    {
        return Http::acceptJson()->asJson()->withHeaders(
            [
                'x-api-key' => $this->apiKey
            ]
        )->baseUrl($this->baseUrl);
    }
}
