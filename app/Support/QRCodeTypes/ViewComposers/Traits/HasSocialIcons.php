<?php

namespace App\Support\QRCodeTypes\ViewComposers\Traits;

trait HasSocialIcons
{
    protected abstract function designValue($key);

    protected function socialIconsSelector()
    {
        return sprintf('.qrcode-type-%s .social-icons', $this::type());
    }

    public abstract static function type();

    public function socialIconsStyles()
    {
        $color = $this->designValue('iconsColor');

        if (empty($color)) {
            return null;
        }

        $pattern = $this->socialIconsSelector() . ' svg * { fill: %s; }';

        return sprintf($pattern, $color);
    }
}
