<?php

namespace App\Models\Traits;

use App\Models\File;
use App\Repositories\FileManager;
use Throwable;

trait HasRelatedModel
{
    public function getRelatedModel($requestedClass = null)
    {
        try {
            // 
            $className = $this->related_model;

            $object = $className::find($this->related_model_id);

            if (!($object instanceof $requestedClass)) {
                return null;
            }

            return $object;
        } catch (Throwable $th) {
            return null;
        }
    }
}
