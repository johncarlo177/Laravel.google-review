<?php

namespace App\Support;

use App\Models\QRCode;
use App\Models\QRCodeWebPageDesign;
use App\Support\QRCodeTypes\ViewComposers\Cache\QRCodeCacheManager;
use App\Support\System\Traits\WriteLogs;

class QRCodeWebPageDesignManager
{
    use WriteLogs;

    public function getDesignOrCreateNewDesignIfNeeded(QRCode $qrcode): QRCodeWebPageDesign
    {
        $design = $this->getDesign($qrcode);

        if (!$design) {
            $design =  $this->createDesign($qrcode, [
                'design' => (object)[]
            ]);
        }

        return $design;
    }

    public function getDesignsOfQRCodeIds($qrcodeIds)
    {
        return QRCodeWebPageDesign::whereIn('qrcode_id', $qrcodeIds)->get();
    }

    public function getDesign(QRCode $qrcode): ?QRCodeWebPageDesign
    {
        return QRCodeWebPageDesign::where('qrcode_id', $qrcode->id)->first();
    }

    public function saveDesign(QRCode $qrcode, $data)
    {
        $design = $this->getDesign($qrcode);

        if (!$design) {
            return $this->createDesign($qrcode, $data);
        }

        if (!empty($data))
            $design->fill($data);

        $design->save();

        return $design;
    }

    private function createDesign(QRCode $qrcode, $data)
    {
        $design = new QRCodeWebPageDesign($data);

        $design->qrcode_id = $qrcode->id;

        $design->save();

        return $design;
    }
}
