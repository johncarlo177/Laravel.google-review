<?php

namespace App\Support;

use App\Interfaces\FileManager;
use App\Models\File;
use App\Models\QRCode;

class GrapesJsStorageManager
{
    private FileManager $files;
    private QRCode $qrcode;
    private QRCodeWebPageDesignManager $designs;

    public function __construct()
    {
        $this->files = app(FileManager::class);

        $this->designs = app(QRCodeWebPageDesignManager::class);
    }

    public function withQRCode(QRCode $qrcode)
    {
        $this->qrcode = $qrcode;

        return $this;
    }

    public function storeBase64($key, $data)
    {
        return $this->store($key, $this->decodeBase64($data));
    }

    private function decodeBase64($data)
    {
        return rawurldecode(base64_decode($data));
    }

    public function store($key, $data)
    {
        if (!$data) return;

        $this->storeFile($key, $data);
    }

    public function load($key)
    {
        return $this->loadFile($key);
    }

    private function design()
    {
        return $this->designs->getDesignOrCreateNewDesignIfNeeded($this->qrcode);
    }

    private function fileKey($key)
    {
        return sprintf('%s::%s', class_basename($this::class), $key);
    }

    private function getFile($key)
    {
        $file = File::find($this->getDesignValue($key));

        return $file;
    }

    private function getDesignValue($key)
    {
        return $this->design()->value($this->fileKey($key));
    }

    private function setDesignValue($key, $value)
    {
        return $this->design()->setValue($this->fileKey($key), $value);
    }

    private function loadFile($key)
    {
        $file = $this->getFile($key);

        if (!$file) return '';

        return $this->files->raw($file);
    }

    private function storeFile($key, $data)
    {
        $file = $this->getFile($key);

        if (!$file) {
            $file = $this->saveFile($key, $data);
        } else {
            $this->files->write($file, $data);
        }

        $this->setDesignValue($key, $file->id);

        return $file;
    }

    private function saveFile($key, $data, $ext = 'txt')
    {
        return $this->files->save(
            name: $key,
            type: $this->files::FILE_TYPE_GENERAL_USE_FILE,
            mime_type: MimeTypeResolver::extensionToMimeType($ext),
            attachable_type: $this->qrcode::class,
            attachable_id: $this->qrcode->id,
            user_id: $this->qrcode->user_id,
            extension: $ext,
            data: $data
        );
    }
}
