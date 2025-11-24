<?php

namespace App\Support\BulkOperation\Export;

use App\Models\QRCode;

abstract class BaseExportItem
{
    public abstract static function fromQRCode(QRCode $qrcode);

    public abstract function toArray();

    public abstract function getCsvColumnNames(): array;
}
