<?php

namespace App\Support\BulkOperation\Result;

use App\Models\BulkOperationResult;
use App\Models\QRCode;

class ImportUrlQRCodesOperationResult extends BaseResultItem
{
    public $id, $svg_url, $name;

    public function __construct()
    {
    }

    public function fromQRCode(QRCode $qrcode): static
    {
        $instance = new static($qrcode);

        $instance->id = $qrcode->id;

        $instance->name = $qrcode->name;

        $instance->svg_url = $qrcode->svg_url();

        return $instance;
    }

    public function fromInstanceResult(BulkOperationResult $result): static
    {
        $instance = new static();

        $instance->id = $result->data['id'];

        $instance->name = $result->data['name'];

        $instance->svg_url = $result->data['svg_url'];

        return $instance;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'svg_url' => $this->svg_url
        ];
    }

    public function toQRCode(): ?QRCode
    {
        return QRCode::find($this->id);
    }
}
