<?php

namespace App\Notifications\Dynamic;

use App\Models\BulkOperationInstance;
use App\Support\BulkOperation\BulkOperationManager;

class BulkOperationCompleted extends Base
{
    private BulkOperationInstance $bulkOperationInstance;

    private BulkOperationManager $operations;

    public static function for(BulkOperationInstance $bulkOperationInstance)
    {
        $instance = new static;

        $instance->bulkOperationInstance = $bulkOperationInstance;

        $instance->operations = new BulkOperationManager();

        return $instance;
    }

    public function slug()
    {
        return 'bulk-operation-completed';
    }

    protected function configVariables()
    {
        return [
            'BULK_OPERATION_NAME' => $this->bulkOperationInstance->name,
            'OPERATION_LINK' => $this
                ->operations
                ->ofInstance($this->bulkOperationInstance)
                ->url(),
        ];
    }

    public function defaultEmailSubject()
    {
        return 'BULK_OPERATION_NAME Completed Successfully';
    }

    public function defaultEmailBody()
    {
        return <<<END_TEMPLATE
# Hello,

Bulk operation is completed successfully.

You can download QR Codes from [here](OPERATION_LINK).

END_TEMPLATE;
    }

    public function defaultSmsBody()
    {
        return <<<TEMPLATE
Hello,

Bulk operation is completed successfully.

You can download QR Codes from [here](DOWNLOAD_LINK).

TEMPLATE;
    }
}
