<?php

namespace App\Support\BulkOperation\Result;

use App\Models\BulkOperationResult;
use App\Models\QRCode;

abstract class BaseResultItem
{
    abstract public function toArray();

    public abstract function fromInstanceResult(BulkOperationResult $result): static;

    public abstract function fromQRCode(QRCode $qrcode): static;

    public abstract function toQRCode(): ?QRCode;
}
