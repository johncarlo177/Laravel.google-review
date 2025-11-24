<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/** 
 * @property BulkOperationInstance bulkOperationInstance
 * @property array data
 * @property int bulk_operation_instance_id
 */
class BulkOperationResult extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'array'
    ];

    public function bulkOperationInstance()
    {
        return $this->belongsTo(BulkOperationInstance::class);
    }
}
