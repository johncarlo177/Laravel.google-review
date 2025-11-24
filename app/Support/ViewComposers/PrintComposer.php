<?php

namespace App\Support\ViewComposers;


use App\Models\QRCode;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Support\LazyCollection;

class PrintComposer extends BaseComposer
{
    use WriteLogs;

    /**
     * @var LazyCollection<QRCode>
     */
    protected $qrcodes = null;

    public static function path(): string
    {
        return 'qrcode.bulk.print';
    }

    public function createSvgScope(QRCode $qrcode)
    {
        $svg = $qrcode->getSvgContent();

        // unique prefix per QR
        $prefix = 'qr' . $qrcode->id . '_';

        // 1. Replace IDs
        $svg = preg_replace_callback('/id="([^"]+)"/', function ($m) use ($prefix) {
            return 'id="' . $prefix . $m[1] . '"';
        }, $svg);

        // 2. Replace url(#...) references
        $svg = preg_replace_callback('/url\(#([^)]+)\)/', function ($m) use ($prefix) {
            return 'url(#' . $prefix . $m[1] . ')';
        }, $svg);

        // 3. Replace xlink:href / href references to IDs
        $svg = preg_replace_callback('/xlink:href="#([^"]+)"/', function ($m) use ($prefix) {
            return 'xlink:href="#' . $prefix . $m[1] . '"';
        }, $svg);

        $svg = preg_replace_callback('/href="#([^"]+)"/', function ($m) use ($prefix) {
            return 'href="#' . $prefix . $m[1] . '"';
        }, $svg);

        return $svg;
    }

    protected function initComposer()
    {
        $this->qrcodes = LazyCollection::make($this->view->getData()['qrcodes']);
    }

    public function getQRCodeBackgroundColor()
    {
        return request()->input('qrcode_background_color');
    }

    public function getCutlineMargin()
    {
        return request()->input('cutline_margin');
    }

    protected function getRowsCount()
    {
        return 3;
    }

    public function getPageHeight()
    {
        $rowsCount = $this->getRowsCount();

        $pageMargins = 0.5 * 2;

        $gapHeights = ($rowsCount - 1) * $this->getGridGap();

        $qrcodeHeights = $rowsCount * $this->getQRCodeHeight();

        return $gapHeights + $qrcodeHeights + $pageMargins;
    }

    public function getQRCodeWidth()
    {
        return request()->input('qrcode_width');
    }

    public function getQRCodeHeight()
    {
        return request()->input('qrcode_height');
    }

    public function getPageWidth()
    {
        return request()->input('page_width');
    }

    public function getGridGap()
    {
        return request()->input('grid_gap');
    }

    public function shouldBreak($i)
    {
        return ($i + 1) % $this->getNumberOfQRCodesPerPage() === 0;
    }

    public function getNumberOfQRCodesPerPage()
    {
        return request()->input('qrcodes_per_page');
    }

    public function getNumberOfPages()
    {
        $number = $this->qrcodes->count() / $this->getNumberOfQRCodesPerPage();

        return ceil($number);
    }

    public function getPageRange()
    {

        $range = range(0, $this->getNumberOfPages());

        return $range;
    }

    public function getPageQRCodes($page)
    {
        $from = $page * $this->getNumberOfQRCodesPerPage();

        $qrcodes = $this->qrcodes->slice(
            $from,
            $this->getNumberOfQRCodesPerPage()
        );

        return $qrcodes;
    }

    public function getSignatureText()
    {
        return request()->input('sheet_signature');
    }

    public function getSignatureImage()
    {
        $id = request()->input('sheet_signature_image');

        return file_url($id);
    }
}
