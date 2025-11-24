<?php

namespace App\Support\Apple;

use App\Models\File;
use App\Models\QRCode;
use App\Repositories\FileManager;
use App\Support\System\Traits\WriteLogs;
use PKPass\PKPass;
use Throwable;

class ApplePassGenerator
{
    use WriteLogs;

    protected QRCode $qrcode;

    protected PKPass $pass;

    public static function withQRCode(QRCode $qrcode)
    {
        $instance = new static;

        $instance->qrcode = $qrcode;

        return $instance;
    }

    public static function empty()
    {
        return new static;
    }

    protected function config($key, $default = null)
    {
        try {
            $config = (array) config('apple_wallet');

            return @$config[$key] ?: $default;
            // 
        } catch (Throwable $th) {
            return $default;
        }
    }

    protected function getPassword()
    {
        return $this->config('password');
    }

    protected function getCertificatePath()
    {
        return $this->path('certificate');
    }

    protected function path($name)
    {
        $id = $this->config($name);

        if (!$id) {
            return;
        }

        $file = File::find($id);

        if (!$file) {
            return;
        }

        $files = new FileManager();

        $path = $files->path($file);

        return $path;
    }

    protected function getPassTypeIdentifier()
    {
        return $this->config('pass_type_identifier');
    }

    protected function getTeamIdentifier()
    {
        return $this->config('team_identifier');
    }

    public function isEnabled()
    {
        return !empty($this->getTeamIdentifier());
    }

    public function generate()
    {
        $this->pass = new PKPass(
            $this->getCertificatePath(),
            $this->getPassword()
        );

        // Pass content
        $data = [
            'description' => $this->qrcode->name,
            'formatVersion' => 1,
            'organizationName' => config('app.name'),
            'passTypeIdentifier' => $this->getPassTypeIdentifier(), // Change this!
            'serialNumber' => $this->qrcode->redirect->slug,
            'teamIdentifier' => $this->getTeamIdentifier(), // Change this!
            'storeCard' => [
                'secondaryFields' => [
                    [
                        'key' => 'name',
                        'label' => '',
                        'value' => $this->qrcode->name,
                    ],
                ]
            ],
            'barcodes' => [
                [
                    'format' => 'PKBarcodeFormatQR',
                    'message' => $this->qrcode->redirect->route,
                    'messageEncoding' => 'iso-8859-1',
                ]
            ],
            'backgroundColor' => $this->config(
                'background_color',
                'rgb(255,255,255)'
            ),
            'foregroundColor' => $this->config(
                'foreground_color',
                'rgb(0,0,0)'
            ),
            'logoText' => config('app.name'),
        ];

        $this->pass->setData($data);

        // Add files to the pass package

        $this->addFile('icon', 'icon.png');
        $this->addFile('icon_2x', 'icon@2x.png');
        $this->addFile('logo', 'logo.png');

        return $this;
    }

    protected function addFile($key, $fallback)
    {
        if (!($path = $this->path($key))) {
            return $this->pass->addFile(
                sprintf('%s/%s/%s', __DIR__, 'images', $fallback),
            );
        }

        $this->pass->addFile(
            path: $path,
            name: $fallback
        );
    }

    public function output()
    {
        $this->pass->create(output: true);
    }

    public function create()
    {
        return $this->pass->create();
    }
}
