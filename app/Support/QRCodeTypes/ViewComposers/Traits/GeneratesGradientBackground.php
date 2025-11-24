<?php

namespace App\Support\QRCodeTypes\ViewComposers\Traits;

use App\Support\Color;

trait GeneratesGradientBackground
{
    public abstract function designValue($key);

    private function gradientBackgroundColorKey()
    {
        return 'backgroundColor';
    }

    private function generateGradientBackground($selector, $colorKey = null)
    {
        if (!$colorKey) {
            $colorKey = $this->gradientBackgroundColorKey();
        }

        $color = $this->designValue($colorKey);

        if (empty($color)) {
            return null;
        }


        if ($this->shouldGenerateGradient()) {
            $pattern = $selector . ' { background-image: linear-gradient(135deg, %s, %s); }';

            $rule = sprintf($pattern, $color, Color::adjustBrightness($color, -0.35));
        } else {
            $rule = sprintf(
                '%s { background-image: none; background-color: %s; }',
                $selector,
                $color
            );
        }


        return $rule;
    }

    private function shouldGenerateGradient()
    {
        $gradientEffect = $this->designValue('background-gradient-effect');

        return $gradientEffect != 'disabled';
    }
}
