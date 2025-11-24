<?php

namespace App\Http\Responses;

use App\Models\Invoice;

class InvoiceResponse extends BaseResponse
{
    /**
     * @param Invoice $record
     */
    protected function singleRecordToArray($record): array
    {
        return [
            'created_at' => $record->created_at->format('Y-m-d H:i'),
            'url' => url('invoice/' . $record->uuid),
            'total' => $record->total
        ];
    }
}
