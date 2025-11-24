<?php

namespace App\Support\QRCodeProcessors\AdvancedShapeProcessors;

use SVG\SVG;

class CouponAdvancedShape extends BaseAdvancedShapeProcessor
{
    protected $id = 'coupon';

    public static function getTextLines()
    {
        return [
            'coupon_text_line_1',
            'coupon_text_line_2',
            'coupon_text_line_3',
        ];
    }

    public static function defaultValues()
    {
        return array_merge(
            parent::defaultValues(),
            [
                'couponLeftColor' => '#e8e8e8',
                'couponRightColor' => '#008080',
            ]
        );
    }

    protected static function defaultInputValue($field, $type, $default)
    {
        if ($type === 'text') {
            switch ($field) {
                case 'coupon_text_line_1':
                    return 'EXCLUSIVE';
                case 'coupon_text_line_2':
                    return 'OFFER';
                case 'coupon_text_line_3':
                    return 'LIMITED TIME OFFER';
            }
        }

        return parent::defaultInputValue($field, $type, $default);
    }

    protected function resizeQrPlaceholder()
    {
    }

    protected function postProcess()
    {
        $this->originalSvg = $this->output->svg;

        $this->originalDoc = $this->originalSvg->getDocument();

        $this->svg = SVG::fromString(
            $this->loadSvgFile()
        );

        $this->doc = $this->svg->getDocument();

        $this->output->svg = $this->svg;

        $this->embedDocument();

        $this->removePlaceholdersFill();

        foreach (static::getTextLines() as $line) {
            if (empty($this->qrcode->design->{sprintf('%stext', $line)})) continue;

            $this->renderText($line, $line);
        }

        $this->renderStyles();

        $this->resizeQrPlaceholder();
    }

    protected function removePlaceholdersFill()
    {
        foreach ($this::getTextLines() as $line) {
            $object = $this->doc->getElementById($line);

            $object->setStyle('fill', 'none');
        }

        $placeholders = ['coupon_right', 'coupon_left'];

        foreach ($placeholders as $ph) {
            $object = $this->doc->getElementById($ph);

            $object->setStyle('fill', null);
        }
    }



    protected function generateStyles()
    {
        $styles = '';

        foreach (static::getTextLines() as $line) {
            $styles .= sprintf(
                '
                #coupon_left {
                    fill: %s;
                }

                #coupon_right {
                    fill: %s;
                }
            ',
                $this->qrcode->design->couponLeftColor,
                $this->qrcode->design->couponRightColor,
            );
        }


        return $styles;
    }
}
