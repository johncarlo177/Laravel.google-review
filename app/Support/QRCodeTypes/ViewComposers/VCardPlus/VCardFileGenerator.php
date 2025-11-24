<?php

namespace App\Support\QRCodeTypes\ViewComposers\VCardPlus;

use App\Interfaces\FileManager;
use App\Models\File;
use App\Repositories\DeviceInfo;
use App\Support\System\MemoryCache;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Str;

class VCardFileGenerator
{
    use WriteLogs;

    private $dataProvider = null;

    private FileManager $files;

    protected $shouldUseFallbackWebsite = true;

    private function __construct()
    {
        $this->files = app(FileManager::class);
    }

    public static function withDataProvider(callable $provider)
    {
        $instance = new static;

        $instance->dataProvider = $provider;

        return $instance;
    }

    private function field($key, $default = null)
    {
        return call_user_func($this->dataProvider, $key) ?? $default;
    }

    public function withoutFallbackWebsite()
    {
        $this->shouldUseFallbackWebsite = false;

        return $this;
    }

    public function buildWebsiteList()
    {
        $fallback = $this->shouldUseFallbackWebsite ? [
            [
                'type' => t('Website'),
                'value' => $this->getWebsite()
            ]
        ] : null;

        return ListBuilder::withValue(
            $this->field('website_list')
        )
            ->withDefaultType(t('Website'))
            ->withFallbackList($fallback)
            ->withStringTransformer([$this, 'transformUrl'])
            ->build();
    }

    private function getWebsite()
    {
        $directWebsiteEnabled = config(
            'vcard.direct_customer_website_in_vcard'
        ) === 'enabled';

        $directWebsite = @$this->getLegacyWebsites()[0];

        if ($directWebsiteEnabled && !empty($directWebsite)) {
            return $directWebsite;
        }

        return request()->fullUrl();
    }

    private function logoBase64()
    {
        $id = $this->field('logo');

        if (!file_url($id)) {
            return null;
        }

        $file = File::find($id);

        $ext = $this->files->extension($file);

        $ext = strtoupper($ext);

        return sprintf(
            'ENCODING=BASE64;TYPE=%s:%s',
            $ext,
            $this->files->base64($file)
        );
    }

    public function buildPhonesList()
    {
        $mobile = $this->field('mobile');
        $phone = $this->field('phone');

        $fallback = [
            [
                'type' => t('Mobile'),
                'value' => $mobile
            ],
            [
                'type' => t('Phone'),
                'value' => $phone
            ],
        ];

        return ListBuilder::withValue(
            $this->field('phones')
        )->withDefaultType(
            t('Phone')
        )
            ->withFallbackList($fallback)
            ->withStringTransformer([$this, 'transformPhone'])
            ->build();
    }

    public function buildEmailList()
    {
        $fallback = [
            [
                'type' => t('Email'),
                'value' => $this->field('email')
            ]
        ];

        return ListBuilder::withValue(
            $this->field('emails')

        )
            ->withDefaultType(t('Email'))
            ->withFallbackList($fallback)
            ->withStringTransformer(
                [$this, 'transformEmail']
            )->build();
    }

    public static function escape($str)
    {
        $str = str_replace(";", '\;', $str);
        $str = str_replace(",", '\,', $str);
        $str = str_replace("\n", " ", $str);
        $str = str_replace("'", "\'", $str);
        return $str;
    }

    public function transformUrl(ListItem $item, $i)
    {
        return $this->addAttributes(
            iPhone: [
                sprintf('url%s.URL:%s', $i + 1, $item->getValue()),
                sprintf('url%s.X-ABLabel:%s', $i + 1, $item->getType()),
            ],
            others: [
                sprintf('URL;TYPE="%s":%s', $item->getType(), $item->getValue()),
            ]
        );
    }

    public function transformEmail(ListItem $item, $i)
    {
        return $this->addAttributes(
            iPhone: [
                sprintf('email%s.EMAIL:%s', $i + 1, $item->getValue()),
                sprintf('email%s.X-ABLabel:%s', $i + 1, $item->getType()),
            ],
            others: [
                sprintf('EMAIL;TYPE="%s":%s', $item->getAndroidType(), $item->getValue())
            ]
        );
    }

    protected function isIphone()
    {
        return MemoryCache::remember(__METHOD__, function () {
            $deviceInfo = new DeviceInfo();

            $os = strtolower($deviceInfo->getOSName());

            $this->logDebug('os = %s', $os);

            return $os === 'ios';
        });
    }

    protected function addAttributes($iPhone, $others)
    {
        if ($this->isIphone()) {
            return implode("\n", $iPhone);
        }

        if (!is_array($others)) {
            $others = [$others];
        }

        return implode("\n", $others);
    }

    public function transformPhone(ListItem $item, $i)
    {
        return $this->addAttributes(
            iPhone: [
                sprintf('tel%s.TEL:%s', $i + 1, $item->getValue()),
                sprintf('tel%s.X-ABLabel:%s', $i + 1, $item->getType()),
            ],
            others: [
                sprintf('TEL;TYPE="%s":%s', $item->getType(), $item->getValue()),

            ]
        );
    }

    public function vcard()
    {
        $firstName = $this->field('firstName');
        $lastName = $this->field('lastName');
        $mobile = $this->field('mobile');
        $phone = $this->field('phone');
        $email = $this->field('email');
        $company = $this->field('company');
        $job = $this->field('job');
        $street = $this->field('street');
        $city = $this->field('city');
        $state = $this->field('state');
        $zip = $this->field('zip');
        $country = $this->field('country');

        $bio = $this->field('bio');

        foreach (get_defined_vars() as $var => $value) {
            $$var = $this->escape($value);
        }

        $logo = $this->logoBase64();

        $logoField = $logo ? "PHOTO;$logo" : null;

        $phones = $this->buildPhonesList()->toString();
        $emails = $this->buildEmailList()->toString();
        $websites = $this->buildWebsiteList()->toString();

        $vcard = <<<END_VCARD
BEGIN:VCARD
VERSION:3.0
$logoField
N:$lastName;$firstName;;;
FN:$firstName $lastName
TITLE:$job
ORG:$company
$websites
$emails
$phones
ADR:;;$street;$city;$state;$zip;$country
NOTE:$bio
END:VCARD
END_VCARD;

        return $vcard;
    }

    public function vcardFileName()
    {
        return Str::kebab($this->field('firstName') . ' ' . $this->field('lastName') . '.vcard');
    }

    private function getLegacyWebsites()
    {
        $websites = $this->field('websites', '');

        $websites = explode("\n", $websites);

        $websites = collect($websites);

        if ($websites->isEmpty()) {
            return collect()->add($this->field('website'));
        }

        return collect($websites)
            ->filter(
                fn($w) => !empty(trim($w))
            )->map(function ($w) {
                if (!Str::startsWith($w, 'http')) {
                    $w = "http://$w";
                }
                return $w;
            })
            ->values();
    }

    public function script()
    {
        return view(
            'qrcode.components.vcard-file-generator',
            ['generator' => $this]
        )->render();
    }
}
