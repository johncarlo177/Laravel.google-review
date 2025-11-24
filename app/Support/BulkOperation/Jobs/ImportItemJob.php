<?php

namespace App\Support\BulkOperation\Jobs;

use App\Models\BulkOperationInstance;
use App\Support\BulkOperation\BaseBulkOperation;
use App\Support\BulkOperation\BaseImportOperation;
use App\Support\BulkOperation\Import\BaseImportItem;
use App\Support\System\Traits\WriteLogs;

class ImportItemJob extends BaseJob
{
    use WriteLogs;

    private BaseImportItem $item;

    public function __construct(BaseImportItem $item)
    {
        parent::__construct();

        $this->item = $item;
    }

    protected function getOperationInstance(): BulkOperationInstance
    {
        return $this->item->getOperationInstance();
    }

    protected function getOperation(): BaseBulkOperation
    {
        $instance = $this->item->getOperationInstance();

        $operation =  $this->operations->ofInstance($instance);

        return $operation;
    }

    protected function getImportOperation(): BaseImportOperation
    {
        return $this->getOperation();
    }

    protected function run()
    {
        $this->logDebugf(json_encode($this->item, JSON_PRETTY_PRINT));

        $qrcode = $this->item->saveQRCode();

        $this
            ->getImportOperation()
            ->addResult($qrcode, $this->getOperationInstance());
    }
}
