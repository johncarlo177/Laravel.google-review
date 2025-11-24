<?php

namespace App\Repositories;

use Closure;
use App\Interfaces\MachineTranslation as MachineTranslationInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleTranslation implements MachineTranslationInterface
{
    private function listLanguages()
    {
        $request = $this->request()
            ->get('languages', ['key' => $this->key()]);
    }

    public function translate(string $text, string $from, string $to)
    {
        $request = $this->request()
            ->get('', [
                'q' => $text,
                'source' => $from,
                'target' => $to,
                'format' => 'text',
                'key' => $this->key()
            ]);

        $result = @$request['data']['translations'][0]['translatedText'];

        if (!$result) {
            Log::error('Google Translation returned: ', $request->json());
        }

        return $result;
    }

    private function request()
    {
        return Http::baseUrl(
            'https://translation.googleapis.com/language/translate/v2'
        );
    }

    public function translateLanguage(string $data, string $to, Closure $saver)
    {
        $language = json_decode($data, true);

        foreach ($language as $key => $value) {
            if (!empty($value)) {
                continue;
            }

            try {
                $language[$key] = $this->translate($key, 'en', $to);

                $saver(
                    json_encode($language, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
            } catch (\Throwable $th) {
                Log::error("Unable to translate $key using Google Translation " . $th->getMessage());
                Log::error($th->getTraceAsString());
            }
        }
    }

    private function key()
    {
        $key = json_decode(config('services.google.api_key'));

        if (empty($key)) {
            $key = config('services.google.api_key');
        }

        return $key;
    }
}
