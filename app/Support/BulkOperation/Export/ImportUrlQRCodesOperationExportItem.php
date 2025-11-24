<?php

namespace App\Support\BulkOperation\Export;

use App\Models\QRCode;

class ImportUrlQRCodesOperationExportItem extends BaseExportItem
{
    public $id, $url, $name, $route, $pincode, $advancedShape, $slug;

    public function __construct()
    {
    }

    public static function fromQRCode(QRCode $qrcode)
    {
        $instance = new static();

        $instance->id = $qrcode->id;

        $instance->url = @$qrcode->data->url;

        $instance->name = $qrcode->name;

        $instance->route = @$qrcode->redirect->route;

        $instance->advancedShape = @$qrcode->design->advancedShape;

        $instance->pincode = @$qrcode->pincode;

        $instance->slug = @$qrcode->redirect->slug;

        return $instance;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'name' => $this->name,
            'route' => $this->route,
            'pincode' => $this->pincode,
            'advancedShape' => $this->advancedShape,
            'slug' => $this->slug
        ];
    }

    public function getCsvColumnNames(): array
    {
        return [
            t('ID'),
            t('Destination URL'),
            t('QR Code Name'),
            t('QR Code Route (Read Only).'),
            t('PIN Code'),
            t('Sticker'),
            t('Slug')
        ];
    }
}
