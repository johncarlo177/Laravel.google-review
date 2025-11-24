<?php

namespace App\Support\QRCodeTypes\ViewComposers\Traits;

trait HasWhiteCards
{
    protected abstract function designValue($key);

    public abstract static function type();

    protected function whiteCardSelector()
    {
        return sprintf('.qrcode-type-%s .white-card', $this::type());
    }

    public function whiteCardStyles()
    {
        $color = $this->designValue('iconsColor');

        if (empty($color)) {
            return null;
        }

        $pattern = $this->whiteCardSelector() . ' header svg * { fill: %s; }';

        return sprintf($pattern, $color);
    }
}
