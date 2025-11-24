<?php

namespace App\Support\QRCodeProcessors\Traits;

use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGMask;

trait JoinsForegroundMasks
{
    private function getJoinedMaskId()
    {
        $class = new \ReflectionClass(static::class);

        return $class->getStaticPropertyValue('joinedMaskId');
    }

    protected function processJoinsForegroundMasks()
    {
        $this->makeJoinedMask();

        $this->makeDarkRect();
    }

    protected function makeDarkRect()
    {
        $s = $this->getViewBoxStart();

        $w = $this->getSvgViewBoxSize();

        $rect = new SVGRect($s, $s, $w, $w);

        $rect->setAttribute('mask', sprintf('url(#%s)', $this->getJoinedMaskId()));

        $rect->setAttribute('class', 'dark type-data');

        $this->addChild($this->getForegroundContainer(), $rect);
    }

    protected function makeJoinedMask()
    {
        $container = $this->getForegroundContainer();

        $elems = $container->getElementsByClassName('dark');

        $mask = new SVGMask();

        $mask->setAttribute('id', $this->getJoinedMaskId());

        foreach ($elems as $elem) {
            $mask->addChild($elem);

            $elem->setAttribute('class', '');

            $elem->setAttribute('fill', 'white');
        }

        $this->doc->addChild($mask);
    }

    protected function getForegroundContainer()
    {
        return $this->doc->getElementsByClassName('foreground-0')[0];
    }
}
