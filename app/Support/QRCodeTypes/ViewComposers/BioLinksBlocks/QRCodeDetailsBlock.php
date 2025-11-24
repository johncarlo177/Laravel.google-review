<?php

namespace App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks;

use App\Models\QRCode;
use App\Support\QRCodeTypes\ViewComposers\Base;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\CssRuleGenerator;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\Style\TextFontStyle;
use App\Support\System\MemoryCache;
use App\Support\ViewComposers\BaseComposer;

class QRCodeDetailsBlock extends BaseBlock
{
    public static function slug()
    {
        return 'qrcode-details';
    }

    protected function shouldRender(): bool
    {
        return true;
    }

    protected function generateStyles()
    {
        return CssRuleGenerator::withSelector(
            sprintf('%s', $this->blockSelector())
        )
            ->withModel($this->model)
            ->rule('background-color', 'backgroundColor')
            ->rule('color', 'textColor')
            ->rule('border', fn() => '1px solid transparent')
            ->rule('border-radius', 'borderRadius', 'rem')
            ->rule('border-width', 'border_width', 'px')
            ->rule('border-color', 'border_color')
            ->rule('padding', 'padding', 'px')
            ->generate();
    }

    /**
     * @return QRCode
     */
    public function getSubjectQRCode()
    {
        return MemoryCache::remember(
            __METHOD__,
            function () {
                return QRCode::bySlug($this->getQRCodeSlug());
            }
        );
    }

    public function getQRCodeSlug()
    {
        $parts = explode('/', request()->path());

        $pathSlug = $parts[count($parts) - 1];

        return request()->input('slug') ?: $pathSlug;
    }

    public function getScansCount()
    {
        return $this->getSubjectQRCode()?->scans_count;
    }

    public function getScansText()
    {
        $count = $this->getScansCount();

        return $count > 1 ? t('Scans') : t('Scan');
    }

    protected function fontStyles()
    {
        return TextFontStyle::withSelector(
            sprintf('%s', $this->blockSelector())
        )
            ->withModel($this->model)
            ->generate();
    }

    public function hasScanLimit()
    {
        return $this->getSubjectQRCode()->allowed_scans > 0;
    }

    public function getScanLimit()
    {
        return $this->getSubjectQRCode()->allowed_scans;
    }

    public function didReachLimit()
    {
        return $this->hasScanLimit() && $this->getScanLimit() <= $this->getScansCount();
    }
}
