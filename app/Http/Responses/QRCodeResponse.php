<?php

namespace App\Http\Responses;

use App\Interfaces\FileManager;
use App\Models\File;
use App\Models\QRCodeTemplate;

class QRCodeResponse extends BaseResponse
{
    private FileManager $files;

    public function __construct()
    {
        $this->files = app(FileManager::class);
    }

    /**
     * @param QRCodeTemplate $record
     */
    protected function listRecordToArray($record): array
    {
        return $this->singleRecordToArray($record);
    }

    private function templateImageSource(QRCodeTemplate $template)
    {
        $fileId = $template->screenshot_id;

        $file = File::find($fileId);

        if (!$file) return null;

        return $this->files->url($file);
    }

    protected function singleRecordToArray($record): array
    {
        return [
            'id' => $record->id,
            'name' => $record->name,
            'description' => $record->description,
            'screenshot_url' => $this->templateImageSource($record),
            'screenshot_id' => $record->screenshot_id,
            'template_access_level' => $record->template_access_level,
            'user_id' => $record->user_id,
            'type' => $record->type,
            'category_id' => $record->template_category_id,
            'redirect' => $record->redirect
        ];
    }
}
