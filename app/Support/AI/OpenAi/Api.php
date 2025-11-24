<?php

namespace App\Support\AI\OpenAi;

use App\Support\AI\OpenAi\Models\Response;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Http;

class Api
{
    use WriteLogs;

    protected $input = [];

    protected static $apiKey = null;

    public static function setApiKey($key)
    {
        static::$apiKey = $key;
    }

    public static function withInput($input)
    {
        $instance = new static;

        $instance->input = $input;

        return $instance;
    }

    public function send()
    {
        $raw = $this->request()->post(
            'responses',
            [
                'model' => $this->getModel(),
                'input' => $this->input
            ]
        );

        return Response::make($raw->json());
    }

    protected function getModel()
    {
        return 'gpt-4.1';
    }

    protected function getSecretKey()
    {
        if ($this::$apiKey) {
            return $this::$apiKey;
        }

        return config('services.openai.secret_key');
    }

    protected function request()
    {
        return Http::asJson()
            ->acceptJson()
            ->withToken($this->getSecretKey())
            ->baseUrl(
                'https://api.openai.com/v1'
            );
    }
}
