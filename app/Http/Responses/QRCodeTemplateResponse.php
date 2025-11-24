<?php

namespace App\Http\Responses;

use App\Interfaces\FileManager;
use App\Models\File;
use App\Models\QRCodeTemplate;

class QRCodeTemplateResponse extends BaseResponse
{
    /**
     * @param QRCodeTemplate $record
     */
    protected function listRecordToArray($record): array
    {
        return $this->singleRecordToArray($record);
    }

    protected function singleRecordToArray($record): array
    {
        return [
            'id' => $record->id,
            'name' => $record->name,
            'description' => $record->description,
            'screenshot_url' => file_url($record->screenshot_id),
            'screenshot_id' => $record->screenshot_id,
            'video_id' => $record->video_id,
            'video_url' => file_url($record->video_id),
            'template_access_level' => $record->template_access_level,
            'user_id' => $record->user_id,
            'type' => $record->type,
            'category_id' => $record->template_category_id,
            'redirect' => $record->redirect
        ];
    }
}
