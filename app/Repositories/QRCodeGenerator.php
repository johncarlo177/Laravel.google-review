<?php

namespace App\Repositories;

use App\Interfaces\QRCodeGenerator as IQRCodeGenerator;
use App\Models\Config;
use App\Models\QRCode;
use App\Support\AI\PNGQRCodeGenerator;
use App\Support\ConfigHelper;
use App\Support\QRCodeOutput;
use App\Support\QRCodeStorage;
use App\Support\QRCodeTypes\QRCodeTypeManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Throwable;

class QRCodeGenerator implements IQRCodeGenerator
{
    use WriteLogs;

    protected $type;

    protected $data;

    protected $design;

    protected $outputType;

    protected $size;

    protected $model;

    protected static $processors = [];

    protected $renderText = true;

    private QRCodeTypeManager $typeManager;

    public function __construct()
    {
        $this->typeManager = new QRCodeTypeManager();
    }

    public function init(QRCode $model, string $outputType)
    {
        $this->model = $model;
        $this->outputType = $outputType;
        $this->size = config('qrcode.preview_size');
    }

    public function initFromRequest(Request $request)
    {
        if (!empty($request->id)) {
            $model = QRCode::find($request->id);

            if ($model) {
                $this->model = $model;
            }
        }

        if (empty($this->model)) {
            $this->model = new QRCode;
        }

        $this->model->type = $request->type;

        $this->model->data = json_decode(urldecode($request->data));

        $this->model->design = json_decode(urldecode($request->design));

        $this->outputType = $request->outputType;

        $this->size = $request->size ?: config('qrcode.preview_size');

        $this->renderText = $request->boolean('renderText', true);
    }


    public function writeString()
    {
        $output = $this->pipe();

        return $output;
    }

    public static function processor($processor)
    {
        static::$processors[] = $processor;
    }

    public function saveVariants(QRCode $model)
    {
        $this->saveSvgFile($model);
        $this->savePngFileIfNeeded($model);
    }

    protected function shouldSavePngFile()
    {
        $value = Config::get('qrcode.generate_simple_png_file');

        return $value === 'enabled';
    }

    protected function savePngFileIfNeeded(QRCode $model)
    {
        if (!ConfigHelper::shouldSavePngFile()) {
            return;
        }

        $generator = new PNGQRCodeGenerator($model);

        $generator->generate();
    }

    protected function saveSvgFile(QRCode $model)
    {
        $this->logDebug('Saving SVG file...');

        $generator = new static;

        $generator->init($model, 'svg');

        $this->logDebug('Init done...');

        $content = $generator->writeString();

        $this->logDebug('Saving svg file...');

        QRCodeStorage::ofQRCode($model)->store($content);

        $this->logDebug('SVG file saved...');
    }

    public function respondInline()
    {
        $this->outputType = 'svg';

        $content = $this->writeString();

        return QRCodeStorage::serveSvgContent($content);
    }

    protected function pipe()
    {
        $data = $this->typeManager->find($this->model->type)->makeData(
            $this->model
        );

        $this->logDebug('ID [%s] QR Code Content = %s', $this->model->id, $data);

        $output = new QRCodeOutput($this->model, '', $this->size, $data, 0, $this->renderText);

        $pipeline = new Pipeline(app());

        $this->logDebug('Pipe created');

        $pipeline->send($output)->through(static::$processors);

        $this->logDebug('Pipe has been sent ...');

        $output = $pipeline->thenReturn();

        $this->logDebug('Output generated');

        $output->round = 1;

        $this->logDebug('Doing another round');

        $pipeline->send($output)->through($this::$processors);

        try {
            $output = $pipeline->thenReturn();
        } catch (Throwable $th) {
            $this->logDebug($th->getMessage());
        }

        $this->logDebug('Final output has been generated');

        return $output;
    }

    protected function makeContentType()
    {
        $contentType = 'image/png';

        if (!empty($this->outputType)) {
            return [
                'svg' => 'image/svg+xml',
                'png' => 'image/png'
            ][$this->outputType];
        }

        return $contentType;
    }
}
