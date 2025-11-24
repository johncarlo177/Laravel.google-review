<?php

namespace App\Support\QRCodeProcessors;

use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGMask;
use SVG\Nodes\Structures\SVGSymbol;

class DarkMaskProcessor extends BaseProcessor
{
    public static $darkMaskId = 'dark-mask';

    private $dataTypes = [];

    protected function process()
    {
        $this->initDataTypes();

        $this->makeSymbolsBasedOnDataTypes();

        $this->makeMasksBasedOnDataTypes();

        $this->makeDarkMask();

        $this->makeLayeredGroups(5, 'foreground');

        $container = $this->doc->getElementsByClassName('foreground-0')[0];

        $this->makeForegrounds($container);
    }

    protected function shouldProcess(): bool
    {
        return true;
    }

    protected function makeForegrounds($container)
    {
        foreach ($this->dataTypes as $type) {
            $rect = new SVGRect(0, 0, $this->getSvgViewBoxSize(), $this->getSvgViewBoxSize());

            $rect->setAttribute('class', 'dark type-' . $type);

            $rect->setAttribute('mask', sprintf('url(#%s)', 'mask-' . $type));

            $container->addChild($rect);
        }
    }

    protected function makeMasksBasedOnDataTypes()
    {
        foreach ($this->dataTypes as $type) {
            $mask = new SVGMask();
            $mask->setAttribute('id', 'mask-' .  $type);
            $use = $this->use(
                'symbol-' . $type,
                0,
                0
            );
            $use->setAttribute('fill', 'white');
            $mask->addChild($use);
            $this->doc->addChild($mask);
        }
    }

    protected function makeDarkMask()
    {
        $symbols = $this->doc->getElementsByClassName('qrcode-symbol');

        $mask = new SVGMask();

        $mask->setAttribute('id', $this::$darkMaskId);

        foreach ($symbols as $symbol) {
            $use = $this->use(
                $symbol,
                $this->getViewBoxStart(),
                $this->getViewBoxStart(),
                $this->getSvgViewBoxSize(),
                $this->getSvgViewBoxSize()
            );

            $use->setAttribute('fill', 'white');

            $mask->addChild($use);
        }

        $this->doc->addChild($mask);

        return $mask;
    }

    protected function makeSymbolsBasedOnDataTypes()
    {
        foreach ($this->dataTypes as $type) {

            $path = $this->doc->getElementsByClassName('type-' . $type)[0];

            $symbol = new SVGSymbol();

            $symbol->setAttribute('id', 'symbol-' . $type);

            $symbol->setAttribute('class', 'qrcode-symbol ' . $type);

            $path->setAttribute('class', '');

            $symbol->addChild($path);

            $this->doc->addChild($symbol);
        }
    }

    protected function getPathType($node)
    {
        $class = $node->getAttribute('class');

        preg_match('/type-([a-zA-Z\-]+)/', $class, $match);

        return $match[1];
    }

    protected function initDataTypes()
    {
        $this->doc = $this->svg->getDocument();

        $darkPaths = $this->doc->getElementsByClassName('dark');

        foreach ($darkPaths as $path) {
            $type = $this->getPathType($path);
            $this->dataTypes[] = $type;
        }
    }
}
