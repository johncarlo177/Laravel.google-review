<?php

namespace App\Support\QRCodeDataMakers;

use App\Interfaces\QRCodeDataMaker;
use Illuminate\Support\Facades\Log;

class PaypalMaker extends BaseMaker implements QRCodeDataMaker
{
    protected function verify()
    {
        return true;
    }

    protected function makeData(): string
    {
        $vars = [
            'type',
            'email',
            'amount',
            'shipping',
            'tax',
            'item_name',
            'item_id'
        ];

        foreach ($vars as $var) {
            $$var = '';
            if (isset($this->qrcode->data->$var)) {
                $$var = trim($this->qrcode->data->$var);
            }
        }

        $data = [
            'cmd' => $type,
            'amount' => $amount,
            'business' => $email,
            'item_name' => $item_name,
            'item_id' => $item_id,
            'currency_code' => $currency_code,
            'shipping' => $shipping,
            'tax_rate' => $tax,
        ];

        Log::debug('currency code ', $currency_code);

        $appends = [
            '_xclick' => 'button_subtype=services&bn=PP-BuyNowBF%3Abtn_buynow_LG.gif%3ANonHostedGuest&lc=US&no_note=0',
            '_cart' => 'button_subtype=products&add=1&bn=PP-ShopCartBF%3Abtn_cart_LG.gif%3ANonHostedGuest&lc=US&no_note=0',
            '_donations' => 'bn=PP-DonationsBF%3Abtn_donate_LG.gif%3ANonHostedGuest&lc=US&no_note=0'
        ];

        $url = "https://www.paypal.com/cgi-bin/webscr";

        $query = http_build_query($data);

        $result = "$url?$query&" . $appends[$type];

        return $result;
    }
}
