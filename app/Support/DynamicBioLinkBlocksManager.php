<?php

namespace App\Support;

use App\Interfaces\FileManager;
use App\Models\DynamicBioLinkBlock;
use App\Models\File;
use Illuminate\Http\Request;

class DynamicBioLinkBlocksManager
{
    private FileManager $files;

    public function __construct()
    {
        $this->files = app(FileManager::class);
    }

    public function save($data)
    {
        $block = new DynamicBioLinkBlock();

        if (isset($data['id'])) {
            $block = DynamicBioLinkBlock::find($data['id']);
        }

        $block->fill($data);

        $block->save();

        $this->syncIconAttachableId($block);

        return $block;
    }

    public function delete(DynamicBioLinkBlock $block)
    {
        $block->delete();

        return $block;
    }

    public function storeFile(Request $request)
    {
        $request->merge([
            'attachable_type' => DynamicBioLinkBlock::class,
            'attachable_id' => null,
            'type' => FileManager::FILE_TYPE_DYNAMIC_BIOLINK_BLOCK_FILE
        ]);

        return $this->files->store($request);
    }

    public function getIcon(DynamicBioLinkBlock $block): ?File
    {
        return File::find($block->icon_id);
    }

    private function syncIconAttachableId(DynamicBioLinkBlock $block)
    {
        if ($icon = $this->getIcon($block)) {
            $icon->attachable_id = $block->id;

            $icon->save();
        }
    }
}
