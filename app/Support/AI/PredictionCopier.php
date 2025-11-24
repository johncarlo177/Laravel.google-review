<?php

namespace App\Support\AI;

use App\Interfaces\FileManager;
use App\Models\QRCode;
use App\Models\QuickQrArtPrediction;
use App\Support\System\Traits\WriteLogs;

class PredictionCopier
{
    use WriteLogs;

    private QRCode $from, $to;

    private QuickQrArtPrediction $prediction, $clonedPrediction;

    private FileManager $files;

    private function __construct()
    {
        $this->files = app(FileManager::class);
    }

    public static function from(QRCode $from)
    {
        $instance = new static;

        $instance->from = $from;

        $instance->prediction = QuickQrArtPrediction::ofQRCode($instance->from);

        return $instance;
    }

    public function to(QRCode $to)
    {
        $this->to = $to;

        return $this;
    }

    public function copy()
    {
        $this->clonePrediction();

        $this->cloneOutputFile();

        $this->save();
    }

    private function save()
    {
        $this->clonedPrediction->save();
    }

    private function cloneOutputFile()
    {
        $file = $this->prediction->getOutputFile();

        $this->clonedPrediction->output_file_id = $this->files->duplicate($file)?->id;
    }

    private function clonePrediction()
    {
        $data = $this->prediction->toArray();

        unset($data['id']);

        $this->clonedPrediction = new QuickQrArtPrediction();

        $this->clonedPrediction->forceFill($data);

        $this->clonedPrediction->api_id = sprintf(
            'cloned_from_%s_time_%s_____%s___%s',
            $this->prediction->id,
            time(),
            $this->clonedPrediction->api_id,
            uniqid(),
        );

        $this->clonedPrediction->qrcode_id = $this->to->id;
    }
}
