<?php

namespace App\Http\Controllers;

use App\Models\QRCode;
use App\Models\User;
use App\Plugins\PluginManager;
use App\Support\GrapesJsStorageManager;
use Illuminate\Support\Facades\Crypt;

class GrapesJsController extends Controller
{
    private GrapesJsStorageManager $storage;

    public function __construct()
    {
        $this->storage = new GrapesJsStorageManager;
    }

    private function user(): ?User
    {
        return request()->user();
    }

    private function token()
    {
        return request()->token;
    }

    public function generateWebsiteBuilderUrl(QRCode $qrcode)
    {
        return [
            'url' => url('/website-builder?token=' . $this->encodeToken($qrcode))
        ];
    }

    private function authorizeToken()
    {
        if (!$this->getTokenQRCode() || !$this->getTokenUser()) {
            abort(401, t('Invalid URL'));
        }
    }

    public function viewWebsiteBuilderPage()
    {
        $this->authorizeToken();

        $path = 'blue.website-builder.page';

        $path = PluginManager::doFilter(
            name: PluginManager::FILTER_WEBSITE_BUILDER_PAGE_PATH,
            value: $path
        );

        return view($path, [
            'user' => $this->getTokenUser(),
            'qrcode' => $this->getTokenQRCode()
        ]);
    }

    private function encodeToken(QRCode $qrcode)
    {
        $payload = [
            time(),
            $qrcode->id,
            $this->user()->id,
        ];

        return Crypt::encryptString(json_encode($payload));
    }

    private function decodeToken()
    {
        $payload = Crypt::decryptString($this->token());

        $payload = json_decode($payload);

        return $payload;
    }

    private function getTokenUser()
    {
        return User::find(@$this->decodeToken()[2]);
    }

    private function getTokenQRCode()
    {
        return QRCode::find(@$this->decodeToken()[1]);
    }

    public function store($type)
    {
        $this->authorizeToken();

        return $this->storage->withQRCode(
            $this->getTokenQRCode()
        )->storeBase64($type, request()->input('payload'));
    }

    public function load($type)
    {
        $this->authorizeToken();

        return $this->storage->withQRCode(
            $this->getTokenQRCode()
        )->load($type);
    }
}
