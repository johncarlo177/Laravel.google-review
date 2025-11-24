<?php

namespace App\Support\QRCodeTypes\ViewComposers;

use App\Rules\UrlRule;
use App\Support\Color;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\BlockModel;
use App\Support\QRCodeTypes\ViewComposers\BioLinksBlocks\UpiBlock;
use App\Support\QRCodeTypes\ViewComposers\Traits\GeneratesGradientBackground;
use App\Support\QRCodeTypes\ViewComposers\Traits\HasBusinessHours;
use App\Support\QRCodeTypes\ViewComposers\Traits\HasReviewSites;
use App\Support\QRCodeTypes\ViewComposers\Traits\HasSocialIcons;
use App\Support\QRCodeTypes\ViewComposers\Traits\HasWhiteCards;

class BusinessProfile extends Base
{
    use HasSocialIcons;
    use HasWhiteCards;
    use GeneratesGradientBackground;
    use HasBusinessHours;
    use HasReviewSites;

    public static function type()
    {
        return 'business-profile';
    }

    protected function templateColors($key)
    {
        return [
            'bakery' => [
                'backgroundColor' => '#ff8929',
                'iconsColor' => null,
                'textColor' => '#ffffff',
            ],
            'healthcare' => [
                'backgroundColor' => '#1fceb5',
                'iconsColor' => '#668fb0',
                'textColor' => '#ffffff',
            ],
            'restaurant' => [
                'backgroundColor' => '#fd4921',
                'iconsColor' => '#9f4f2c',
                'textColor' => '#ffffff',
            ],
            'plumber' => [
                'backgroundColor' => '#ffde38',
                'iconsColor' => '#db0000',
                'textColor' => '#39372d',
            ],
            'barber' => [
                'backgroundColor' => '#ffd780',
                'iconsColor' => '#4c3d00',
                'textColor' => '#4c3d00'
            ],
            'electrician' => [
                'backgroundColor' => '#fbfbe9',
                'iconsColor' => '#db2424',
                'textColor' => '#342d2d'
            ],
            'builder' => [
                'backgroundColor' => '#ffce00',
                'iconsColor' => '#007fb0',
                'textColor' => '#212121'
            ],
            'gardener' => [
                'backgroundColor' => '#58bd00',
                'iconsColor' => '#8c5f12',
                'textColor' => '#ffffff'
            ],
            'cafe' => [
                'backgroundColor' => '#daa669',
                'iconsColor' => '#925817',
                'textColor' => '#fdf7ed'
            ],
            'mechanic' => [
                'backgroundColor' => '#323950',
                'iconsColor' => '#5c71c7',
                'textColor' => '#ffffff'
            ],
            'garage' => [
                'backgroundColor' => '#21222e',
                'iconsColor' => '#ff616d',
                'textColor' => '#ededed'
            ],
            'joiner' => [
                'backgroundColor' => '#b38759',
                'iconsColor' => '#f4af46',
                'textColor' => '#f5f5f5'
            ],
            'car-valeter' => [
                'backgroundColor' => '#d7d7d7',
                'iconsColor' => '#b93d49',
                'textColor' => '#413535'
            ],
            'painter' => [
                'backgroundColor' => '#9e9e9e',
                'iconsColor' => '#f0d111',
                'textColor' => '#eeede9'
            ],
            'plaster' => [
                'backgroundColor' => '#7c7b64',
                'iconsColor' => '#6e6d59',
                'textColor' => '#fffafa'
            ],
            'cleaner' => [
                'backgroundColor' => '#009ec8',
                'iconsColor' => '#0059c2',
                'textColor' => '#e0eaf5'
            ],
            'roofer' => [
                'backgroundColor' => '#8bb1dc',
                'iconsColor' => '#3c5f87',
                'textColor' => '#ededed'
            ],
            'accountant' => [
                'backgroundColor' => '#dbe1e8',
                'iconsColor' => '#749692',
                'textColor' => '#352e2e'
            ],
            'solicitor' => [
                'backgroundColor' => '#7f5a28',
                'iconsColor' => '#77592d',
                'textColor' => '#fff8f5'
            ],
            'other' => [
                'backgroundColor' => '#6ea9cf',
                'iconsColor' => '#4842aa',
                'textColor' => '#ffffff',
            ],
        ][$this->qrcodeData('businessType')][$key];
    }

    public function logo()
    {
        return $this->fileUrl('logo') ?? asset(
            sprintf(
                '/assets/images/business-profile/%s/logo.svg',
                $this->qrcodeData('businessType', 'bakery')
            )
        );
    }

    public function favicon()
    {
        return $this->fileUrl('favicon');
    }

    public function detailsContainerStyles()
    {
        return $this->generateGradientBackground(
            '.qrcode-type-business-profile .details-container',
            'backgroundColor'
        );
    }

    public function mainDetailsStyles()
    {
        $color = $this->designValue('backgroundColor');

        if (empty($color)) {
            return null;
        }

        $selector = 'html .qrcode-type-business-profile .main-details';

        $shortSelector = 'html .qrcode-type-business-profile';

        $pattern = "$selector p, $selector h1, $shortSelector .portfolio-title { color: %s; }";

        return sprintf($pattern, $this->designValue('textColor', Color::getContrastColor($color)));
    }

    public function shouldRenderPortfolio()
    {
        return !empty($this->designValue('portfolio')) && is_array($this->designValue('portfolio'));
    }

    public function portfolio()
    {
        $items = $this->designValue('portfolio');

        if (!is_array($items)) return [];

        $sorted = collect($items)->sort(function ($i1, $i2) {
            $s1 = is_numeric(@$i1['sort_order']) ? @$i1['sort_order'] : 100;
            $s2 = is_numeric(@$i2['sort_order']) ? @$i2['sort_order'] : 100;

            return $s1 - $s2;
        });

        return $sorted->values()->all();
    }

    public function portfolioItemImage($item)
    {
        return $this->findFileUrl(
            @$item['image'],
            override_asset('/assets/images/image-placeholder.svg', true)
        );
    }

    public function getWebsiteUrl()
    {
        return UrlRule::forValue($this->qrcodeData('websiteUrl'))
            ->parse();
    }

    public function renderUpiBlock()
    {
        $block = $this->getUpiBlock();

        if (!$block) return;

        return implode("\n", [$block->render($this), $block->styles()]);
    }


    protected function getUpiBlock()
    {
        if (empty($this->designField('upi_block'))) {
            return;
        }

        $model = new BlockModel($this->designField('upi_block'));

        $block = (new UpiBlock)->withModel($model);

        return $block;
    }
}
