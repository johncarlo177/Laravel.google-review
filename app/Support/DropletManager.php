<?php

namespace App\Support;

use App\Support\QRCodeTypes\QRCodeTypeManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Http;

class DropletManager
{
    use WriteLogs;

    private ConfigFileManager $configs;

    public function __construct()
    {
        $this->configs = new ConfigFileManager;
    }

    public static function boot()
    {
        $instance = new static;

        QRCodeTypeManager::registerFilter([$instance, 'filterQRCodeTypes']);
    }

    public function verify()
    {
        if (!config('app.installed')) {
            return;
        }

        $code = $this->code();

        $composer = json_decode(
            file_get_contents(
                base_path('composer.json')
            ),
            true
        );

        $version = $composer['version'];

        $name = $composer['name'];

        $http = Http::acceptJson()->timeout(20);

        if (!config('app.http_client_verify_ssl')) {
            $http->withoutVerifying();
        }

        $marketplace = config('app.marketplace');

        $url = url('/');

        $requestUrl = $this->bd(
            'aHR0cHM6Ly9xdWlja2NvZGUuZGlnaXRhbC9hcGkvdmVyaWZ5Lw=='
        ) . $code;

        $response = $http->get(
            $requestUrl,
            compact('name', 'version', 'marketplace', 'url')
        );

        $pass = $this->getResponsePass($response);

        if (!$pass) {
            return $this->handleInvalidDroplet();
        }

        $type = $this->getResponseType($response);

        $this->saveType($type);

        $this->saveIsLarge();

        $this->saveDidRun();
    }

    private function getType()
    {
        return config('droplet.type');
    }

    public function isLarge()
    {
        $type = $this->bd($this->getType());

        $pattern = sprintf('/%s/i', $this->bd('ZXh0ZW5kZWQ='));

        return preg_match($pattern, $type) === 1;
    }

    private function saveIsLarge()
    {
        $this->configs->save('droplet.is_large', $this->isLarge());
    }

    public function isSmall()
    {
        return !$this->isLarge();
    }

    public function filterQRCodeTypes(QRCodeTypes\BaseType $type)
    {
        $largeTypes = [
            'YnVzaW5lc3MtcHJvZmlsZQ==', // b-p
            'dmNhcmQtcGx1cw==', // v-p
            'cmVzdGF1cmFudC1tZW51', // r-m
            'cHJvZHVjdC1jYXRhbG9ndWU=', // p-c
            'YmlvbGlua3M=', // b-l
            'bGVhZC1mb3Jt', // l-f
            'cmVzdW1l', // r
            'ZmlsZS11cGxvYWQ=', // f-u
            'YXBwLWRvd25sb2Fk', // a-d
            'd2Vic2l0ZS1idWlsZGVy', // w-b
            'YnVzaW5lc3MtcmV2aWV3', // b-r
            'dXBpLWR5YW5taWM=', // u-d
        ];

        $typeIsLarge = array_search(
            $this->be($type->slug()),
            $largeTypes
        ) !== false;

        if ($typeIsLarge) {
            return $this->isLarge();
        }

        return true;
    }

    private function saveType($type)
    {
        $this->configs->save('droplet.type', base64_encode($type));
    }

    private function saveDidRun()
    {
        if (config('droplet.did_run')) return;

        $this->configs->save('droplet.did_run', true);
    }

    public function didRun()
    {
        if (config('droplet.did_run')) return true;

        return false;
    }

    public function isValid()
    {
        if (empty($this->code())) return false;

        if (config('droplet.is_invalid')) {
            return false;
        }

        return true;
    }

    private function handleInvalidDroplet()
    {
        $this->configs->save('droplet.is_invalid', true);
    }

    private function getResponsePass($response)
    {
        return $response[$this->bd('dmVyaWZpZWQ=')];
    }

    private function getResponseType($response)
    {
        return $response[$this->bd('bGljZW5zZQ==')];
    }

    private function code()
    {
        return config($this->bd('YXBwLnB1cmNoYXNlX2NvZGU='));
    }

    private function be($a)
    {
        $b = 'POIUWEJbXaEsTeG6FG4L_JFYGeALLOUWnWcWoEdTOPe';

        $b = preg_replace('/[A-Z]/', '', $b);

        return $b($a);
    }

    private function bd($a)
    {
        $bd = 'XZAQbQWERYVCaASDFQWsEQWEReQWER6KJHHJKKS4SDFGG_GGdQEEReTcDDDoSSdDXAQERe';

        $bd = preg_replace('/[A-Z]/', '', $bd);

        return $bd($a);
    }
}
