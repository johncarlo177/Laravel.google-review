<?php

namespace App\Models\Traits;

use App\Models\File;
use App\Repositories\FileManager;

trait DuplicatesSelf
{

    protected function fileKeys()
    {
        return [];
    }

    public function duplicate()
    {
        $data = $this->toArray();

        unset($data['id']);

        $files = new FileManager;

        foreach ($this->fileKeys() as $key) {
            $file = File::find($data[$key]);

            if (!$file) {
                continue;
            }

            $newFile = $files->duplicate($file);

            $data[$key] = $newFile->id;
        }

        $clone = new static;

        $clone->forceFill($data);

        $clone->save();

        return $clone;
    }
}
