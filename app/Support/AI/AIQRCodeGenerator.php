<?php

namespace App\Support\AI;

use App\Events\ShouldSaveQRCodeVariants;
use App\Exceptions\MonthlyLimitReached;
use App\Interfaces\FileManager;
use App\Interfaces\SubscriptionManager;
use App\Models\Config;
use App\Models\QRCode;
use App\Models\QuickQrArtInput;
use App\Models\QuickQrArtPrediction;
use App\Support\DropletManager;
use App\Support\QRCodeTypes\QRCodeTypeManager;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\Facades\Http;

class AIQRCodeGenerator
{
    use WriteLogs;

    private QRCodeTypeManager $qrcodeTypes;

    private QuickQRArtAPI $api;

    private FileManager $files;

    private DropletManager $droplet;

    private SubscriptionManager $subscriptions;


    public function __construct()
    {
        $this->qrcodeTypes = app(QRCodeTypeManager::class);

        $this->api = new QuickQRArtAPI();

        $this->files = app(FileManager::class);

        $this->droplet = app(DropletManager::class);

        $this->subscriptions = app(SubscriptionManager::class);
    }

    public function hasAiDesign(QRCode $qrcode)
    {
        if ($this->droplet->isSmall()) return false;

        if (!$this->isEnabled()) return false;

        if (!$qrcode->design->is_ai) return false;

        $prediction = QuickQrArtPrediction::ofQRCode($qrcode);

        return $prediction?->status === QuickQrArtPrediction::STATUS_EXECUTED;
    }

    public function duplicateAiDesign(QRCode $from, QRCode $to)
    {
        if (!$this->hasAiDesign($from)) {
            $this->logDebug('QRCode %s doesnt have AI design', $from->id);

            return;
        }

        PredictionCopier::from($from)
            ->to($to)
            ->copy();
    }


    public function isEnabled()
    {
        return !empty(Config::get('quickqr_art.api_key'));
    }

    /**
     * @param float $qrStrength  QR Weight determines how much the final image will portray your QR. Range: 0.0 – 3.0 Default: 0.85
     * @param int $qrSteps Balance steps is how many time the image is sampled. More steps maybe more artistic but also reduce the QR scannability. Range: 10 – 20. Default: 16
     */
    public function queue(
        QRCode $qrcode,
        $prompt,
        $negativePrompt,
        $qrStrength,
        $qrSteps,
        $shortModelVersion
    ) {
        if (
            $this->subscriptions->userAiGenerationsLimitReached($qrcode->user)
        ) {
            throw new MonthlyLimitReached(t('Monthly limit reached.'));
        }

        $pngGenerator = new PNGQRCodeGenerator($qrcode);

        $url = $pngGenerator->generate();

        $response = $this->api->queue(
            QuickQrArtInput::init(
                workflow: QuickQrArtInput::resolveShortVersion(
                    $shortModelVersion
                ),
                qrStrength: $qrStrength,
                qrImage: $url,
                qrText: $prompt,
                negativePrompt: $negativePrompt,
                qrSteps: $qrSteps,
                webhook: url('/webhooks/quickqrart'),
                qrContent: $qrcode->getContent(),
            )
        );

        $this->logDebugf('Got API response %s', json_encode($response, JSON_PRETTY_PRINT));

        $prediction = QuickQrArtPrediction::fromApiResponse(@$response['data'], $qrcode);

        return $prediction;
    }



    private function validateUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE);
    }

    public function savePredictionResponse($api_id, $output, $status)
    {
        $output = is_array($output) ? $output[0] : $output;

        /**
         * @var QuickQrArtPrediction
         */
        $prediction = QuickQrArtPrediction::where('api_id', $api_id)->first();

        if (!$prediction) {
            $this->logErrorf(
                'Prediction not found in quick_qr_art_predictions table %s',
                $api_id
            );

            return;
        }

        $prediction->status = $status;

        $prediction->save();

        if (!$prediction->isSuccess()) {
            $this->logWarningf(
                'Prediction is not successfull %s',
                json_encode($prediction, JSON_PRETTY_PRINT)
            );

            return;
        }

        if (!$output || !$this->validateUrl($output)) {
            $this->logWarningf(
                'Invalid output URL %s',
                json_encode($output, JSON_PRETTY_PRINT)
            );

            return;
        }

        UsageManager::forUser($prediction->user)->increaseUsage();

        $this->logDebugf('Saving output URL %s', $output);

        $generatedPngFile = Http::withOptions([
            'decode_content' => false
        ])->get($output)->body();

        $file = $this->files->save(
            name: sprintf('ai-generated-image-%s.png', $prediction->qrcode->id),
            type: $this->files::FILE_TYPE_GENERAL_USE_FILE,
            mime_type: 'image/png',
            attachable_type: $prediction::class,
            attachable_id: $prediction->id,
            user_id: $prediction->user_id,
            extension: 'png',
            data: $generatedPngFile
        );

        $prediction->output_file_id = $file->id;

        $prediction->save();

        event(new ShouldSaveQRCodeVariants($prediction->qrcode));

        $this->logDebugf('File saved %s %s', $file->id, $this->files->url($file));
    }

    /**
     * @param string $pngBase64 base64 of the png data
     * @return string svg string with the PNG image embedded inside
     */
    public function pngToSvg($path)
    {
        $png = base64_encode(file_get_contents($path));

        $size = getimagesize($path);

        list($width, $height) = $size;

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%1$s" height="%2$s" viewBox="0 0 %1$s %2$s">
                <image x="0" y="0" width="%1$s" height="%2$s" href="%3$s">
                </image>
            </svg>',
            $width,
            $height,
            'data:image/png;base64,' . $png
        );

        return $svg;
    }

    public function buildSvgString(QRCode $qrcode)
    {
        if (!$this->hasAiDesign($qrcode)) {
            return null;
        }

        $prediction = QuickQrArtPrediction::ofQRCode($qrcode);

        if (!$prediction->getOutputFile()) return null;

        $svg = '';

        $this->files->useTempLocalFile(
            $prediction->getOutputFile(),
            function ($path) use ($prediction, &$svg) {

                $svg = $this->pngToSvg($path);
            }
        );

        return $svg;
    }
}
