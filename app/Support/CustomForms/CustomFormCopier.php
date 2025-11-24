<?php

namespace App\Support\CustomForms;

use App\Models\CustomForm;
use App\Models\File;
use App\Models\QRCode;
use App\Repositories\FileManager;
use App\Support\QRCodeWebPageDesignManager;
use App\Support\System\Traits\WriteLogs;

class CustomFormCopier
{
    use WriteLogs;

    protected QRCode $source;
    protected QRCode $destination;

    public static function withSourceQRCode(QRCode $source)
    {
        $instance = new static;

        $instance->source = $source;

        return $instance;
    }

    public function to(QRCode $destination)
    {
        $this->destination = $destination;

        return $this;
    }

    protected function getDesign(QRCode $qrcode)
    {
        return (new QRCodeWebPageDesignManager)->getDesign($qrcode);
    }

    protected function getSourceDesign()
    {
        return $this->getDesign($this->source);
    }

    protected function getDestinationDesign()
    {
        return $this->getDesign($this->destination);
    }

    public function copy()
    {
        $sourceFormId = @$this->getSourceDesign()->design['automatic_form_popup'];

        if (!$sourceFormId) {
            return;
        }

        $sourceForm = CustomForm::find($sourceFormId);

        if (!$sourceForm) {
            return;
        }

        $sourceArray = $sourceForm->toArray();

        unset($sourceArray['id']);

        $destinationForm = new CustomForm();

        $destinationForm->forceFill($sourceArray);

        $destinationForm->related_model_id = $this->destination->id;

        $destinationForm->settings = array_merge(
            $destinationForm->settings,
            [
                'header_image' => $this->copyHeaderImage($destinationForm->settings)?->id,
            ]
        );

        $this->logDebug('Settings %s', $destinationForm->settings);

        $destinationForm->save();

        $webDesign = $this->getDestinationDesign();

        $webDesign->design = array_merge($webDesign->design, [
            'automatic_form_popup' => $destinationForm->id
        ]);

        $webDesign->save();
    }

    protected function copyHeaderImage($settings)
    {
        $headerImageId = @$settings['header_image'];

        if (!$headerImageId) {
            return null;
        }

        $file = File::find($headerImageId);

        if (!$file) {
            return null;
        }

        return (new FileManager())->duplicate($file);
    }
}
