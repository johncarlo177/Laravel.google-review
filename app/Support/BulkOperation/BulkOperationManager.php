<?php

namespace App\Support\BulkOperation;

use App\Models\BulkOperation;
use App\Models\BulkOperationInstance;
use App\Support\System\Traits\ClassListLoader;

class BulkOperationManager
{
    use ClassListLoader;

    /**
     * @return BaseBulkOperation[]
     */
    public function listTypes()
    {
        return $this->operations()
            ->map(function (BaseBulkOperation $operation) {
                return $operation->toArray();
            });
    }

    public function find($type): BaseBulkOperation
    {
        return $this->operations()->first(
            function (BaseBulkOperation $op) use ($type) {
                return $op->type() === $type;
            }
        );
    }

    public function ofInstance(BulkOperationInstance $instance): ?BaseBulkOperation
    {
        return $this
            ->find($instance->type);
    }

    private function operations()
    {
        return collect(
            $this->makeInstances(
                __DIR__ . '/Operations',
                __NAMESPACE__ . '\\Operations'
            )
        );
    }
}
