<?php

namespace App\Support;

use App\Interfaces\FileManager;
use App\Models\File;
use App\Models\QRCode;
use App\Models\QRCodeWebPageDesign;

class QRCodeAttachedFiles
{
    private QRCode $qrcode;

    private QRCodeWebPageDesignManager $design;

    private FileManager $files;

    public function __construct()
    {
        $this->design = new QRCodeWebPageDesignManager();

        $this->files = app(FileManager::class);
    }

    public static function withQRCode(QRCode $qrcode)
    {
        $instance = new static;

        $instance->qrcode = $qrcode;

        return $instance;
    }

    public function deleteFiles()
    {
        collect($this->getFiles())->each(function (File $file) {
            $this->files->delete($file);
        });
    }

    /**
     * @return int[]
     */
    public function getFileIds()
    {
        return array_map(fn ($file) => $file->id, $this->getFiles());
    }

    /**
     * @return File[]
     */
    public function getFiles()
    {
        $data = (array) $this->qrcode->data;

        $design = $this->designArray();

        return $this->extractFiles(array_merge(
            $data,
            $design
        ));
    }

    private function isPotentialsIdField($value)
    {
        return is_string($value) || is_int($value);
    }

    private function extractFiles($fields)
    {
        $files = [];

        array_walk_recursive(
            $fields,
            function ($value) use (&$files) {
                if (!$this->isPotentialsIdField($value)) {
                    return;
                }

                /**
                 * @var File
                 */
                $file = @File::find($value);

                if (!$file) return;

                $exists = $file && $this->files->exists($file);

                if (
                    $exists &&
                    $file->attachable_type === QRCode::class ||
                    $file->attachable_type === QRCodeWebPageDesign::class
                ) {
                    $files[] = $file;
                }
            }
        );

        return $files;
    }

    private function designArray()
    {
        $arr = [];

        $design = $this->design->getDesign($this->qrcode);

        if (!empty($design)) {
            $arr = (array) $design->design;
        }

        return $arr;
    }
}
