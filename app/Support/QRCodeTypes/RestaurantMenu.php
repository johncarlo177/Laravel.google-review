<?php

namespace App\Support\QRCodeTypes;

use App\Http\Middleware\ErrorMessageMiddleware;
use App\Models\QRCode;



class RestaurantMenu extends BaseDynamicType
{
    public static function name(): string
    {
        return t('Restaurant Menu');
    }

    public static function slug(): string
    {
        return 'restaurant-menu';
    }

    public function rules(): array
    {
        return [
            'restaurant_name' => 'required',
            'website' => 'url'
        ];
    }

    public function generateName(QRCode $qrcode): string
    {
        return sprintf(
            '%s - %s',
            $qrcode->data->restaurant_name,
            t('Menu')
        );
    }

    public function generateTableQRCodes(QRCode $qrcode, $from, $to)
    {
        $url = $qrcode->redirect->route;

        $result = [];

        if ($to - $from > 70) {
            return ErrorMessageMiddleware::abortWithMessage(
                'Cannot generate more than 70 tables QR Codes.'
            );
        }

        for ($i = $from; $i < $to + 1; $i++) {
            $clone = new QRCode();
            $clone->type = 'url';

            $clone->design = $qrcode->design;
            $clone->user_id  = $qrcode->user_id;

            $clone->data = [
                'url' => url(
                    $url . "?table-number=$i"
                )
            ];

            $clone->name = sprintf('%s [%s] - %s', t('Table'), $i, $qrcode->name);

            $clone->save();

            $result[] = $clone;
        }

        return $result;
    }

    public function shouldCacheView()
    {
        return false;
    }
}
