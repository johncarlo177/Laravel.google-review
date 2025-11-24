<?php

namespace App\Support\QRCodeTypes;

use App\Models\File;
use App\Models\QRCode;
use App\Repositories\FileManager;
use App\Support\QRCodeTypes\Interfaces\ShouldImmediatlyRedirectToDestination;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Str;

class FileUpload extends BaseDynamicType implements ShouldImmediatlyRedirectToDestination
{
    use WriteLogs;

    public static function name(): string
    {
        return t('File Upload');
    }

    public static function slug(): string
    {
        return 'file-upload';
    }

    public function rules(): array
    {
        return [
            'file_id' => 'required',
            'name' => 'required',
        ];
    }

    public function generateName(QRCode $qrcode): string
    {
        $name = Str::limit($qrcode->data->name, 50);

        return empty($name) ? t('File Upload') : $name;
    }

    public function makeDestination(QRCode $qrcode): string
    {
        $fileId = @$qrcode->data->file_id;

        $file = File::find($fileId);

        if (!$file) {
            return '';
        }

        $fs = new FileManager();

        return $fs->url($file);
    }
}
