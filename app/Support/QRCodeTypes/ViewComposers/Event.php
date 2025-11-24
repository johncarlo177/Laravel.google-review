<?php

namespace App\Support\QRCodeTypes\ViewComposers;

use App\Models\File;
use App\Support\Color;
use App\Support\QRCodeStorage;
use App\Support\QRCodeTypes\ViewComposers\Traits\GeneratesButtonStyles;

use App\Support\QRCodeTypes\ViewComposers\Traits\GeneratesGradientBackground;
use App\Support\QRCodeTypes\ViewComposers\Traits\HasSocialIcons;
use App\Support\QRCodeTypes\ViewComposers\Traits\HasWhiteCards;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Event extends Base
{
    use HasWhiteCards;
    use HasSocialIcons;
    use GeneratesGradientBackground;
    use GeneratesButtonStyles;

    public static function type()
    {
        return 'event';
    }

    protected function getTemplate()
    {
        return 'default';
    }

    public function qrcodeUrl()
    {
        $mtime = QRCodeStorage::ofQRCode($this->qrcode)->getSvgModificationTime();

        return $this->qrcode->svg_url() . '?v=' . $mtime;
    }

    public function gradientBgStyles()
    {
        return $this->generateGradientBackground(
            '.qrcode-type-event .details-container .gradient-bg',
            'backgroundColor'
        );
    }

    public function secondBackgroundStyles()
    {
        return $this->generateGradientBackground(
            '.qrcode-type-event .details-container',
            'secondBackgroundColor'
        );
    }


    public function addToCalendarButtonStyles()
    {
        $colors = [
            'bg' => $this->designValue('addToCalendarButtonColor'),
            'color' => $this->designValue('addToCalendarButtonTextColor')
        ];

        $selector = '.qrcode-type-event .classic-add-to-contacts add-to-calendar-button ';

        $rules = [
            'bg' => '--blue-primary-0: %1$s; --blue-primary-1: %1$s;',
            'color' => '--btn-text: %s;'
        ];

        $css = array_reduce(array_keys($colors), function ($result, $color) use ($rules, $colors, $selector) {
            if (empty($colors[$color])) {
                return;
            }

            $rule = sprintf($rules[$color], $colors[$color]);

            $result[] = sprintf('%s { %s }', $selector, $rule);

            return $result;
        }, []);

        if (empty($css)) return '';

        return implode('', $css);
    }

    public function registerButtonStyles()
    {
        return $this->bindButtonStyles(
            bgColorKey: 'registerButtonColor',
            textColorKey: 'registerButtonTextColor',
            buttonSelector: '.qrcode-type-event .classic-add-to-contacts .button '
        );
    }



    public function textColorsStyles()
    {
        $color = $this->designValue('textColor');

        if (empty($color)) {
            return null;
        }

        $selector = 'html .qrcode-type-event .vertical-list';

        $pattern = "$selector { color: %s; }";

        return sprintf(
            $pattern,
            $this->designValue('textColor', Color::getContrastColor($color))
        );
    }

    public function shouldRenderContacts()
    {
        $values = array_map(fn ($item) => $item['value'], $this->contacts());

        $values = array_filter($values);

        return !empty($values);
    }

    public function contacts()
    {
        $defaultContacts = [
            [
                'name' => t('Name'),
                'value' => $this->qrcodeData('contact_name'),
            ],
            [
                'name' => t('Mobile'),
                'value' => $this->qrcodeData('contact_mobile'),
                'link' => 'tel:' . $this->qrcodeData('contact_mobile'),
            ],
            [
                'name' => t('Email'),
                'value' => $this->qrcodeData('contact_email'),
                'link' => 'mailto:' . $this->qrcodeData('contact_email'),
            ]
        ];

        return $defaultContacts;
    }

    public function websites()
    {
        $websites = $this->qrcodeData('websites', '');

        $websites = explode("\n", $websites);

        return collect($websites)
            ->filter(
                fn ($w) => !empty(trim($w))
            )->map(function ($w) {
                if (!Str::startsWith($w, 'http')) {
                    $w = "http://$w";
                }
                return $w;
            })
            ->values();
    }

    public function daysBreakdown()
    {
        $breakdown = (array)$this->qrcodeData('day_breakdown', []);

        $breakdown = array_map(fn ($item) => (array)$item, $breakdown);

        return $breakdown;
    }

    public function dayBreakdownDate($day)
    {
        return $this->formatDate($day['date']);
    }

    public function shouldRenderLogo()
    {
        return !empty($this->fileUrl('logo'));
    }

    private function formatDate($dateString, $forceFormat = null)
    {
        $format = config('event_qrcode_type.date_format');

        if ($forceFormat) {
            $format = $forceFormat;
        }

        switch ($format) {
            case 'dd-mm-yyyy':
                return Carbon::parse($dateString)->format('d-m-Y');

            case 'mm/dd/yyyy':
                return Carbon::parse($dateString)->format('m/d/Y');

            default:
                return Carbon::parse($dateString)->format('Y-m-d');
        }
    }

    public function dates()
    {
        $dates = $this->qrcodeData('day_breakdown');

        $dates = array_map(function ($date) {
            $date = (array)$date;

            return [
                'name' => $date['day_name'],
                'description' => @$date['description'] ?? '',
                'startDate' => $this->formatDate($date['date'], 'Y-m-d'),
                'startTime' => $date['time_from'],
                'endTime' => @$date['time_to'] ?? '',
            ];
        }, $dates);

        return json_encode($dates);
    }

    public function portfolioTitleStyles()
    {
        $color = $this->designValue('portfolio_section_title_color');

        if (empty($color)) return;

        return sprintf('.qrcode-type-event .portfolio-title { color: %s; }', $color);
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

    private function logoBase64()
    {
        if (!$this->shouldRenderLogo()) return;

        $file = File::find($this->designValue('logo'));

        $ext = $this->files->extension($file);

        $ext = strtoupper($ext);

        return sprintf('ENCODING=BASE64;TYPE=%s:%s', $ext, $this->files->base64($file));
    }

    public function faqs()
    {
        $items = $this->designValue('faqs');

        if (empty($items) || !is_array($items)) return [];

        $sorted = collect($items)->sort(function ($c1, $c2) {
            $s1 = is_numeric(@$c1['sort_order']) ? @$c1['sort_order'] : 0;
            $s2 = is_numeric(@$c2['sort_order']) ? @$c2['sort_order'] : 0;

            return $s1 - $s2;
        });

        return $sorted;
    }

    public function faqsMainTitle()
    {
        $default = t('FAQs');

        $value = $this->designValue('faqs_main_title');

        if (empty($value)) return $default;

        return $value;
    }

    public function customAddToCalendarButtonLabels()
    {
        $labels = [
            'close' => t('Close'),
            'modal.multidate.h' => t('This is an event series'),
            'modal.multidate.text' => t('Add the individual events one by one:'),
            'label.addtocalendar' => t('Add to Calendar'),
            'cancel' => t('Cancel'),
            'expired' => t('Expired'),
            'modal.clipboard.text' => t('We automatically copied a magical URL into your clipboard.'),

        ];

        return json_encode($labels, JSON_UNESCAPED_UNICODE);
    }
}
