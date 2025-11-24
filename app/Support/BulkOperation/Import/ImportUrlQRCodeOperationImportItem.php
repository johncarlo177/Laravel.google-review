<?php

namespace App\Support\BulkOperation\Import;

use App\Models\BulkOperationInstance;
use App\Models\QRCode;
use App\Plugins\PluginManager;
use App\Repositories\QRCodeGenerator;
use App\Support\BulkOperation\Support\UrlSignature;
use App\Support\FolderManager;
use App\Support\QRCodeCopier;
use App\Support\System\Traits\WriteLogs;
use Throwable;

class ImportUrlQRCodeOperationImportItem extends BaseImportItem
{
    use WriteLogs;
    private FolderManager $folders;

    /** 
     * Advanced Shape means Sticker.
     */
    public $id,
        $url,
        $name,
        $pincode,
        $advancedShape,
        $folderName,
        $allowedScans,
        $templateId;

    public function __construct()
    {
        $this->folders = new FolderManager;
    }

    protected function init(
        $operationId,
        $id,
        $url,
        $name,
        $pincode = null,
        $advancedShape = null,
        $folderName = null,
        $allowedScans = null,
        $templateId = null,
    ) {
        $this->operationInstanceId = $operationId;

        $this->id = $id;

        $this->url = $url;

        $this->name = $name;

        $this->pincode = $pincode;

        $this->advancedShape = $advancedShape;

        $this->folderName = $folderName;

        $this->allowedScans = intval($allowedScans) ?? null;

        $this->templateId = $templateId;

        return $this;
    }

    public function fromCsvRow(BulkOperationInstance $operation, array $row): static
    {
        $instance = new static;

        return $instance->init(
            operationId: $operation->id,
            id: @$row[0],
            url: @$row[1],
            name: @$row[2],
            pincode: @$row[3],
            advancedShape: @$row[4],
            folderName: @$row[5],
            allowedScans: intval(@$row[6]),
            templateId: @$row[7],
        );
    }

    public function toQRCode(): QRCode
    {
        $qrcode = QRCode::find($this->id) ?? $this->makeNewQRCode();

        $qrcode->type = 'url';

        $qrcode->name = $this->name;

        if ($this->allowedScans >= 0) {
            $qrcode->allowed_scans = $this->allowedScans;
        }

        // Do not initialize advancedShape and pin code in first save.

        $qrcode->user_id ??= $this->getUser()->id;

        // Start QR Code Data

        $data = $qrcode->data ?? (object)[];

        $data->url = $this->url;

        $qrcode->data = $data;

        // End QR Code Data

        // Start QR Code Design

        $design = $qrcode->design ?? (object)[];

        $design->advancedShape = $design->advancedShape ?? $this->advancedShape;

        $qrcode->design = $design;

        // End QR Code Design

        (new QRCodeGenerator)->saveVariants($qrcode);

        return $qrcode;
    }

    private function makeNewQRCode()
    {
        $qrcode = $this->createQRCode();

        $qrcode->is_created_by_bulk_operation = true;

        return $qrcode;
    }

    protected function findTemplate()
    {
        try {
            // 
            return QRCode::find($this->templateId);
            // 
        } catch (Throwable $th) {
            return null;
        }
    }

    protected function createQRCode()
    {
        if (!$this->templateId || is_nan($this->templateId)) {
            return new QRCode();
        }

        $template = $this->findTemplate();

        if (!$template) {
            return new QRCode();
        }

        $copier = new QRCodeCopier();

        $qrcode = $copier->copy($template);

        $qrcode->folder_id  = null;

        $qrcode->save();

        return $qrcode;
    }

    public function saveQRCode(): QRCode
    {
        $qrcode = $this->toQRCode();

        $qrcode->save();

        usleep(300);

        $qrcode->name = $this->substituteVariables($this->name, $qrcode);

        $qrcode->pincode = $this->substituteVariables($this->pincode, $qrcode);

        $design = $qrcode->design ?? (object)[];

        $design->advancedShape = $design->advancedShape ?? $this->advancedShape;

        $qrcode->design = $design;

        $data = $qrcode->data;

        $data->url = $this->substituteVariables($this->url, $qrcode);

        $qrcode->data = $data;

        $qrcode = PluginManager::doFilter(
            PluginManager::FILTER_IMPORT_URL_QRCODE_OPERATION_EXTEND_QRCODE,
            $qrcode
        );

        $qrcode->save();

        $this->assignFolderIfNeeded($qrcode);

        return $qrcode;
    }

    protected function assignFolderIfNeeded($qrcode)
    {
        if (empty($this->folderName)) {

            $qrcode->folder_id = null;

            $qrcode->save();

            return;
        };

        $folder = $this->folders->getOrCreateFolderByFolderName(
            user: $this->getUser(),
            folderName: $this->folderName
        );

        $this->folders->addQRCode($qrcode, $folder);
    }

    protected function substituteVariables($input, QRCode $qrcode)
    {
        $vars = [
            'QRCODE_ID' => $qrcode->id,
            'QRCODE_SLUG' => $qrcode->redirect?->slug,
            'RANDOM_PINCODE' => $this->generateRandomPinCode(),
            'URL_SIGNATURE' => fn($input) => $this->generateSignature(
                'URL_SIGNATURE',
                $input
            )
        ];

        foreach ($vars as $var => $value) {
            if (is_callable($value))
                $input = $value($input);
            else
                $input = str_replace($var, $value, $input);
        }

        return $input;
    }

    private function generateSignature($pattern, $value)
    {
        $generator = new UrlSignature('URL_SIGNATURE', $value);

        return $generator->sign();
    }

    private function generateRandomPinCode()
    {
        return random_int(10000, 99999);
    }

    public function getCsvColumnNames(): array
    {
        return [
            t('ID (Optional)'),
            t('Destination URL (Required)'),
            t('QR Code Name (Required)'),
            t('PIN Code (Optional)'),
            t('Sticker (Optional)'),
            t('Folder Name (Optional)'),
            t('Allowed Scans'),
            t('Template ID'),
        ];
    }

    public function toArray()
    {
        return [
            @$this->id,
            @$this->url,
            @$this->name,
            @$this->pincode,
            @$this->advancedShape,
            @$this->folderName,
            @$this->allowedScans,
            @$this->templateId,
        ];
    }
}
