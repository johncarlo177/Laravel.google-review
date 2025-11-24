<?php

namespace App\Support\BulkOperation\Import;

use App\Models\BulkOperation;
use App\Models\BulkOperationInstance;
use App\Models\QRCode;
use App\Models\User;

abstract class BaseImportItem
{
    protected $operationInstanceId;

    public function __construct()
    {
    }

    public abstract function fromCsvRow(
        BulkOperationInstance $operationInstance,
        array $row
    ): static;

    protected function rules()
    {
        return [];
    }

    public function validate()
    {
    }

    public abstract function toQRCode(): QRCode;

    public function getOperationInstance(): BulkOperationInstance
    {
        return BulkOperationInstance::find($this->operationInstanceId);
    }

    public abstract function saveQRCode(): QRCode;

    public abstract function getCsvColumnNames(): array;

    protected function getUser(): User
    {
        return $this->getOperationInstance()->user;
    }
}
